import mysql from "mysql2/promise";

// สร้าง Connection Pool สำหรับเชื่อมต่อไปยังฐานข้อมูล MySQL (Docker port 3307)
// ใช้ global pool เพื่อป้องกันการสร้าง connection ซ้ำซ้อนตอน Next.js ทำ Fast Refresh
const globalForDb = globalThis;

const pool =
  globalForDb.mysqlPool ||
  mysql.createPool({
    host: process.env.DB_HOST || "127.0.0.1",
    port: Number(process.env.DB_PORT) || 3307,
    user: process.env.DB_USER || "root",
    password: process.env.DB_PASSWORD || "root",
    database: process.env.DB_NAME || "db_student",
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0,
  });

if (process.env.NODE_ENV !== "production") {
  globalForDb.mysqlPool = pool;
}

export default pool;
