require("dotenv").config({ path: `${__dirname}/.env` });

const crypto = require("node:crypto");
const fs = require("node:fs");
const path = require("node:path");
const cors = require("cors");
const express = require("express");
const multer = require("multer");
const mysql = require("mysql2/promise");

const app = express();
const router = express.Router();
const port = Number(process.env.PORT || 3000);
const host = process.env.HOST || "0.0.0.0";
const publicApiUrl = (process.env.PUBLIC_API_URL || `http://localhost:${port}`).replace(/\/+$/, "");
const uploadDirectory = path.resolve(process.env.UPLOAD_DIR || path.join(__dirname, "..", "uploads"));
const allowedImageTypes = new Map([
    ["image/jpeg", "jpg"],
    ["image/png", "png"],
    ["image/gif", "gif"],
    ["image/webp", "webp"]
]);

fs.mkdirSync(uploadDirectory, { recursive: true });

const pool = mysql.createPool({
    host: process.env.DB_HOST || "127.0.0.1",
    port: Number(process.env.DB_PORT || 3306),
    database: process.env.DB_NAME || "pet_adoption",
    user: process.env.DB_USER || "root",
    password: process.env.DB_PASSWORD || "",
    waitForConnections: true,
    connectionLimit: Number(process.env.DB_CONNECTION_LIMIT || 10),
    queueLimit: 0,
    charset: "utf8mb4",
    timezone: "Z"
});

class ApiError extends Error {
    constructor(status, message) {
        super(message);
        this.status = status;
    }
}

function asyncRoute(handler) {
    return (req, res, next) => Promise.resolve(handler(req, res, next)).catch(next);
}

function fail(status, message) {
    throw new ApiError(status, message);
}

function validateId(value, label) {
    const id = Number(value);
    if (!Number.isSafeInteger(id) || id < 1) fail(400, `${label} must be a positive integer.`);
    return id;
}

function petImageUrl(image) {
    if (!image || image === "default.jpg") return null;
    const filename = path.basename(image);
    if (!fs.existsSync(path.join(uploadDirectory, filename))) return null;
    return `${publicApiUrl}/uploads/${encodeURIComponent(filename)}`;
}

function serializePet(pet) {
    return { ...pet, image_url: petImageUrl(pet.image) };
}

function bearerToken(req) {
    const match = /^Bearer\s+([a-f0-9]{64})$/i.exec(req.get("authorization") || "");
    return match ? match[1] : "";
}

async function authenticate(req, res, next) {
    try {
        const token = bearerToken(req);
        if (!token) fail(401, "A valid Bearer token is required.");
        const tokenHash = crypto.createHash("sha256").update(token).digest("hex");
        const [rows] = await pool.execute(
            `SELECT u.id, u.name, u.email, u.role
             FROM api_tokens t
             JOIN users u ON u.id = t.user_id
             WHERE t.token_hash = ? AND t.expires_at > UTC_TIMESTAMP()
             LIMIT 1`,
            [tokenHash]
        );
        if (!rows.length) fail(401, "Token is invalid or expired.");
        req.user = rows[0];
        req.apiTokenHash = tokenHash;
        next();
    } catch (error) {
        next(error);
    }
}

function requireAdmin(req, res, next) {
    if (req.user.role !== "admin") return next(new ApiError(403, "Administrator access is required."));
    next();
}

function validatePet(body) {
    const pet = {
        name: String(body.name || "").trim(),
        breed: String(body.breed || "").trim(),
        species: body.species || "Dog",
        age: body.age === undefined || body.age === "" ? 0 : Number(body.age),
        gender: body.gender || "Male",
        size: body.size || "Medium",
        color: String(body.color || "").trim(),
        description: String(body.description || "").trim(),
        status: body.status || "Available"
    };

    if (!pet.name || pet.name.length > 100) fail(422, "Pet name is required and must be at most 100 characters.");
    if (!["Dog", "Cat", "Bird", "Rabbit", "Other"].includes(pet.species)) fail(422, "Species is invalid.");
    if (!["Male", "Female"].includes(pet.gender)) fail(422, "Gender is invalid.");
    if (!["Small", "Medium", "Large"].includes(pet.size)) fail(422, "Size is invalid.");
    if (!["Available", "Pending", "Adopted"].includes(pet.status)) fail(422, "Pet status is invalid.");
    if (!Number.isSafeInteger(pet.age) || pet.age < 0) fail(422, "Age must be a non-negative whole number.");
    return pet;
}

const uploadImage = multer({
    storage: multer.memoryStorage(),
    limits: { fileSize: 5 * 1024 * 1024, files: 1 },
    fileFilter: (req, file, callback) => {
        if (!allowedImageTypes.has(file.mimetype)) {
            return callback(new ApiError(422, "Only JPG, PNG, GIF, and WebP images are allowed."));
        }
        callback(null, true);
    }
}).single("image");

function optionalImage(req, res, next) {
    uploadImage(req, res, error => {
        if (error) return next(error);
        next();
    });
}

async function storePetImage(file) {
    if (!file) return null;
    const type = file.mimetype;
    const buffer = file.buffer;
    const signatures = {
        "image/jpeg": buffer.length >= 3 && buffer[0] === 0xff && buffer[1] === 0xd8 && buffer[2] === 0xff,
        "image/png": buffer.length >= 8 && buffer.subarray(0, 8).equals(Buffer.from([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a])),
        "image/gif": buffer.length >= 6 && ["GIF87a", "GIF89a"].includes(buffer.toString("ascii", 0, 6)),
        "image/webp": buffer.length >= 12 && buffer.toString("ascii", 0, 4) === "RIFF" && buffer.toString("ascii", 8, 12) === "WEBP"
    };
    if (!signatures[type]) fail(422, "Uploaded file content is not a valid supported image.");
    const filename = `pet_${crypto.randomBytes(12).toString("hex")}.${allowedImageTypes.get(type)}`;
    await fs.promises.writeFile(path.join(uploadDirectory, filename), buffer, { flag: "wx" });
    return filename;
}

async function issueToken(userId) {
    const token = crypto.randomBytes(32).toString("hex");
    const tokenHash = crypto.createHash("sha256").update(token).digest("hex");
    await pool.execute(
        "INSERT INTO api_tokens (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 30 DAY))",
        [userId, tokenHash]
    );
    const [rows] = await pool.execute(
        "SELECT DATE_FORMAT(expires_at, '%Y-%m-%d %H:%i:%s') AS expires_at FROM api_tokens WHERE token_hash = ?",
        [tokenHash]
    );
    return { access_token: token, token_type: "Bearer", expires_at: rows[0].expires_at };
}

async function withTransaction(handler) {
    const connection = await pool.getConnection();
    try {
        await connection.beginTransaction();
        const result = await handler(connection);
        await connection.commit();
        return result;
    } catch (error) {
        await connection.rollback();
        throw error;
    } finally {
        connection.release();
    }
}

const corsOrigin = process.env.CORS_ORIGIN || "*";
app.disable("x-powered-by");
app.use(cors({
    origin: corsOrigin === "*" ? "*" : corsOrigin.split(",").map(value => value.trim()),
    methods: ["GET", "POST", "PATCH", "DELETE", "OPTIONS"],
    allowedHeaders: ["Authorization", "Content-Type", "Accept"]
}));
app.use(express.json({ limit: "1mb" }));
app.use(express.urlencoded({ extended: false, limit: "1mb" }));
app.use((req, res, next) => {
    if (!req.body) req.body = {};
    next();
});
app.use("/uploads", express.static(uploadDirectory, { dotfiles: "deny", index: false, maxAge: "1h" }));

router.get("/health", asyncRoute(async (req, res) => {
    await pool.query("SELECT 1");
    res.json({ status: "ok" });
}));

router.get("/stats", asyncRoute(async (req, res) => {
    const [rows] = await pool.query(
        `SELECT SUM(status = 'Available') AS available_pets,
                SUM(status = 'Adopted') AS adopted_pets,
                (SELECT COUNT(*) FROM users WHERE role = 'user') AS users
         FROM pets`
    );
    const stats = Object.fromEntries(Object.entries(rows[0]).map(([key, value]) => [key, Number(value || 0)]));
    res.json({ data: stats });
}));

router.post("/auth/register", asyncRoute(async (req, res) => {
    const name = String(req.body.name || "").trim();
    const email = String(req.body.email || "").trim();
    const password = String(req.body.password || "");
    if (name.length < 2 || name.length > 100) fail(422, "Name must be between 2 and 100 characters.");
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) || email.length > 100) fail(422, "A valid email address is required.");
    if (password.length < 6) fail(422, "Password must be at least 6 characters.");
    const bcrypt = require("bcryptjs");
    const securePasswordHash = await bcrypt.hash(password, 12);
    const [result] = await pool.execute(
        "INSERT INTO users (name, email, password) VALUES (?, ?, ?)",
        [name, email, securePasswordHash]
    );
    const token = await issueToken(result.insertId);
    res.status(201).json({
        ...token,
        user: { id: result.insertId, name, email, role: "user" }
    });
}));

router.post("/auth/login", asyncRoute(async (req, res) => {
    const email = String(req.body.email || "").trim();
    const password = String(req.body.password || "");
    const [users] = await pool.execute(
        "SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1",
        [email]
    );
    if (!users.length) fail(401, "Email or password is incorrect.");
    const user = users[0];
    const bcrypt = require("bcryptjs");
    const passwordMatches = await bcrypt.compare(password, user.password);
    if (!passwordMatches) fail(401, "Email or password is incorrect.");
    const token = await issueToken(user.id);
    delete user.password;
    res.json({ ...token, user });
}));

router.post("/auth/logout", authenticate, asyncRoute(async (req, res) => {
    await pool.execute(
        "DELETE FROM api_tokens WHERE user_id = ? AND token_hash = ?",
        [req.user.id, req.apiTokenHash]
    );
    res.json({ message: "Signed out successfully." });
}));

router.get("/pets", asyncRoute(async (req, res) => {
    const conditions = [];
    const values = [];
    for (const field of ["species", "size", "gender", "status"]) {
        if (req.query[field]) {
            conditions.push(`${field} = ?`);
            values.push(String(req.query[field]));
        }
    }
    if (!req.query.status) conditions.push("status = 'Available'");
    if (req.query.search) {
        conditions.push("(name LIKE ? OR breed LIKE ?)");
        values.push(`%${String(req.query.search).trim()}%`, `%${String(req.query.search).trim()}%`);
    }
    const limit = Math.max(1, Math.min(100, Number.parseInt(req.query.limit, 10) || 100));
    const where = conditions.length ? `WHERE ${conditions.join(" AND ")}` : "";
    const [pets] = await pool.execute(
        `SELECT id, name, breed, species, age, gender, size, color, description, image, status, created_at
         FROM pets ${where} ORDER BY created_at DESC LIMIT ${limit}`,
        values
    );
    res.json({ data: pets.map(serializePet) });
}));

router.get("/pets/:id", asyncRoute(async (req, res) => {
    const id = validateId(req.params.id, "Pet id");
    const [pets] = await pool.execute(
        `SELECT id, name, breed, species, age, gender, size, color, description, image, status, created_at
         FROM pets WHERE id = ?`,
        [id]
    );
    if (!pets.length) fail(404, "Pet was not found.");
    res.json({ data: serializePet(pets[0]) });
}));

router.get("/me", authenticate, (req, res) => {
    res.json({ data: req.user });
});

router.get("/me/requests", authenticate, asyncRoute(async (req, res) => {
    const [requests] = await pool.execute(
        `SELECT ar.id, ar.pet_id, ar.message, ar.status, ar.created_at,
                p.name AS pet_name, p.breed, p.species, p.image
         FROM adoption_requests ar
         JOIN pets p ON p.id = ar.pet_id
         WHERE ar.user_id = ?
         ORDER BY ar.created_at DESC`,
        [req.user.id]
    );
    res.json({
        data: requests.map(request => ({
            ...request,
            image_url: petImageUrl(request.image)
        }))
    });
}));

router.post("/pets/:id/adoption-requests", authenticate, asyncRoute(async (req, res) => {
    if (req.user.role !== "user") fail(403, "Only adopter accounts can submit adoption requests.");
    const petId = validateId(req.params.id, "Pet id");
    const message = String(req.body.message || "").trim();
    if (!message || message.length > 5000) fail(422, "Message is required and must be at most 5000 characters.");
    const requestId = await withTransaction(async connection => {
        const [pets] = await connection.execute("SELECT status FROM pets WHERE id = ? FOR UPDATE", [petId]);
        if (!pets.length) fail(404, "Pet was not found.");
        if (pets[0].status !== "Available") fail(409, "This pet is not currently available.");
        const [existing] = await connection.execute(
            "SELECT id FROM adoption_requests WHERE user_id = ? AND pet_id = ? LIMIT 1",
            [req.user.id, petId]
        );
        if (existing.length) fail(409, "You have already requested this pet.");
        const [result] = await connection.execute(
            "INSERT INTO adoption_requests (user_id, pet_id, message) VALUES (?, ?, ?)",
            [req.user.id, petId, message]
        );
        await connection.execute("UPDATE pets SET status = 'Pending' WHERE id = ?", [petId]);
        return result.insertId;
    });
    res.status(201).json({ data: { id: requestId, pet_id: petId, status: "Pending" } });
}));

router.get("/admin/dashboard", authenticate, requireAdmin, asyncRoute(async (req, res) => {
    const [rows] = await pool.query(
        `SELECT (SELECT COUNT(*) FROM pets) AS pets,
                (SELECT COUNT(*) FROM pets WHERE status = 'Available') AS available_pets,
                (SELECT COUNT(*) FROM adoption_requests WHERE status = 'Pending') AS pending_requests,
                (SELECT COUNT(*) FROM users WHERE role = 'user') AS users`
    );
    const stats = Object.fromEntries(Object.entries(rows[0]).map(([key, value]) => [key, Number(value)]));
    res.json({ data: stats });
}));

router.get("/admin/requests", authenticate, requireAdmin, asyncRoute(async (req, res) => {
    const [requests] = await pool.query(
        `SELECT ar.id, ar.user_id, u.name AS user_name, u.email, ar.pet_id,
                p.name AS pet_name, ar.message, ar.status, ar.created_at
         FROM adoption_requests ar
         JOIN users u ON u.id = ar.user_id
         JOIN pets p ON p.id = ar.pet_id
         ORDER BY ar.created_at DESC`
    );
    res.json({ data: requests });
}));

router.patch("/admin/requests/:id", authenticate, requireAdmin, asyncRoute(async (req, res) => {
    const requestId = validateId(req.params.id, "Request id");
    const status = req.body.status;
    if (!["Approved", "Rejected"].includes(status)) fail(422, "Status must be Approved or Rejected.");
    const result = await withTransaction(async connection => {
        const [requests] = await connection.execute(
            "SELECT pet_id, status FROM adoption_requests WHERE id = ? FOR UPDATE",
            [requestId]
        );
        if (!requests.length) fail(404, "Adoption request was not found.");
        if (requests[0].status !== "Pending") fail(409, "This request has already been reviewed.");
        await connection.execute("UPDATE adoption_requests SET status = ? WHERE id = ?", [status, requestId]);
        const petStatus = status === "Approved" ? "Adopted" : "Available";
        await connection.execute("UPDATE pets SET status = ? WHERE id = ?", [petStatus, requests[0].pet_id]);
        return { id: requestId, status };
    });
    res.json({ data: result });
}));

router.get("/admin/pets", authenticate, requireAdmin, asyncRoute(async (req, res) => {
    const [pets] = await pool.query(
        `SELECT id, name, breed, species, age, gender, size, color, description, image, status, created_at
         FROM pets ORDER BY created_at DESC`
    );
    res.json({ data: pets.map(serializePet) });
}));

router.post("/admin/pets", authenticate, requireAdmin, optionalImage, asyncRoute(async (req, res) => {
    const pet = validatePet(req.body);
    const image = await storePetImage(req.file) || "default.jpg";
    try {
        const [result] = await pool.execute(
            `INSERT INTO pets (name, breed, species, age, gender, size, color, description, status, image)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
            [pet.name, pet.breed, pet.species, pet.age, pet.gender, pet.size, pet.color, pet.description, pet.status, image]
        );
        res.status(201).json({ data: { id: result.insertId, image } });
    } catch (error) {
        if (image !== "default.jpg") await fs.promises.unlink(path.join(uploadDirectory, image)).catch(() => {});
        throw error;
    }
}));

router.patch("/admin/pets/:id", authenticate, requireAdmin, optionalImage, asyncRoute(async (req, res) => {
    const id = validateId(req.params.id, "Pet id");
    const [existingRows] = await pool.execute(
        `SELECT name, breed, species, age, gender, size, color, description, status, image
         FROM pets WHERE id = ?`,
        [id]
    );
    const existing = existingRows[0];
    if (!existing) fail(404, "Pet was not found.");
    const pet = validatePet({ ...existing, ...req.body });
    const uploadedImage = await storePetImage(req.file);
    const image = uploadedImage || existing.image;
    try {
        await pool.execute(
            `UPDATE pets
             SET name = ?, breed = ?, species = ?, age = ?, gender = ?, size = ?, color = ?,
                 description = ?, status = ?, image = ?
             WHERE id = ?`,
            [pet.name, pet.breed, pet.species, pet.age, pet.gender, pet.size, pet.color, pet.description, pet.status, image, id]
        );
    } catch (error) {
        if (uploadedImage) await fs.promises.unlink(path.join(uploadDirectory, uploadedImage)).catch(() => {});
        throw error;
    }
    if (uploadedImage && /^pet_[a-f0-9]{24}\.(jpg|png|gif|webp)$/.test(existing.image)) {
        await fs.promises.unlink(path.join(uploadDirectory, existing.image)).catch(error => {
            console.error("Could not remove replaced pet image:", error.message);
        });
    }
    res.json({ data: { id, image } });
}));

router.delete("/admin/pets/:id", authenticate, requireAdmin, asyncRoute(async (req, res) => {
    const id = validateId(req.params.id, "Pet id");
    const [pets] = await pool.execute("SELECT id FROM pets WHERE id = ?", [id]);
    if (!pets.length) fail(404, "Pet was not found.");
    const [requests] = await pool.execute("SELECT id FROM adoption_requests WHERE pet_id = ? LIMIT 1", [id]);
    if (requests.length) fail(409, "Pets with adoption request history cannot be deleted.");
    await pool.execute("DELETE FROM pets WHERE id = ?", [id]);
    res.json({ message: "Pet deleted." });
}));

router.get("/admin/users", authenticate, requireAdmin, asyncRoute(async (req, res) => {
    const [users] = await pool.query(
        "SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC"
    );
    res.json({ data: users });
}));

router.delete("/admin/users/:id", authenticate, requireAdmin, asyncRoute(async (req, res) => {
    const id = validateId(req.params.id, "User id");
    if (id === Number(req.user.id)) fail(409, "You cannot delete your own account.");
    const [users] = await pool.execute("SELECT id FROM users WHERE id = ?", [id]);
    if (!users.length) fail(404, "User was not found.");
    const [requests] = await pool.execute(
        "SELECT id FROM adoption_requests WHERE user_id = ? LIMIT 1",
        [id]
    );
    if (requests.length) fail(409, "Users with adoption request history cannot be deleted.");
    await pool.execute("DELETE FROM users WHERE id = ?", [id]);
    res.json({ message: "User deleted." });
}));

app.use("/api/v1", router);

app.use((req, res) => {
    res.status(404).json({ error: { message: "API endpoint was not found." } });
});

app.use((error, req, res, next) => {
    if (res.headersSent) return next(error);
    if (error instanceof ApiError) {
        return res.status(error.status).json({ error: { message: error.message } });
    }
    if (error instanceof SyntaxError && error.status === 400 && "body" in error) {
        return res.status(400).json({ error: { message: "Request body must be valid JSON." } });
    }
    if (error instanceof multer.MulterError) {
        const status = error.code === "LIMIT_FILE_SIZE" ? 413 : 400;
        const message = error.code === "LIMIT_FILE_SIZE"
            ? "Image must be 5 MB or smaller."
            : "The uploaded image could not be processed.";
        return res.status(status).json({ error: { message } });
    }
    if (error && error.code === "ER_DUP_ENTRY") {
        return res.status(409).json({ error: { message: "An account with this email already exists." } });
    }
    console.error("API request failed:", error);
    res.status(500).json({ error: { message: "The request could not be completed." } });
});

async function start() {
    await pool.query("SELECT 1");
    const server = app.listen(port, host, () => {
        console.log(`PetAdopt API listening at http://${host}:${port}/api/v1`);
    });

    for (const signal of ["SIGINT", "SIGTERM"]) {
        process.on(signal, () => {
            server.close(async () => {
                await pool.end();
                process.exit(0);
            });
        });
    }
}

start().catch(async error => {
    console.error("Could not start the API server:", error.message);
    await pool.end();
    process.exit(1);
});
