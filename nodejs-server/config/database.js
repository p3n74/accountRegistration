const mysql = require('mysql2/promise');

const dbConfig = {
  host: process.env.DB_HOST || '127.0.0.1',
  user: process.env.DB_USER || 's21102134_palisade',
  password: process.env.DB_PASSWORD || 'webwebwebweb',
  database: process.env.DB_NAME || 's21102134_palisade',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  charset: 'utf8mb4',
  acquireTimeout: 60000,
  timeout: 60000,
  reconnect: true
};

// Create connection pool
const pool = mysql.createPool(dbConfig);

// Test connection
async function testConnection() {
  try {
    const connection = await pool.getConnection();
    console.log('✅ Database connected successfully');
    connection.release();
    return true;
  } catch (error) {
    console.error('❌ Database connection failed:', error.message);
    return false;
  }
}

// Execute query with error handling
async function executeQuery(query, params = []) {
  try {
    const [results] = await pool.execute(query, params);
    return results;
  } catch (error) {
    console.error('Database query error:', error);
    throw error;
  }
}

// Get event participants
async function getEventParticipants(eventId) {
  const query = `
    SELECT 
      participant_id,
      event_id,
      uid,
      email,
      attendance_status,
      registered,
      joined_at
    FROM event_participants 
    WHERE event_id = ? 
    ORDER BY joined_at DESC
  `;
  return executeQuery(query, [eventId]);
}

// Get event details
async function getEventDetails(eventId) {
  const query = `
    SELECT 
      eventid,
      eventname,
      eventcreator,
      participantcount,
      startdate,
      enddate,
      location
    FROM events 
    WHERE eventid = ?
  `;
  const results = await executeQuery(query, [eventId]);
  return results[0] || null;
}

// Update participant count
async function updateParticipantCount(eventId) {
  const query = `
    UPDATE events 
    SET participantcount = (
      SELECT COUNT(*) 
      FROM event_participants 
      WHERE event_id = ?
    ) 
    WHERE eventid = ?
  `;
  return executeQuery(query, [eventId, eventId]);
}

module.exports = {
  pool,
  testConnection,
  executeQuery,
  getEventParticipants,
  getEventDetails,
  updateParticipantCount
}; 