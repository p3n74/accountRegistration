const chokidar = require('chokidar');
const path = require('path');
const fs = require('fs');

function startWatching(io) {
  const participantsPath = path.join(__dirname, '../../storage/participants');
  const eventsPath = path.join(__dirname, '../../storage/events');

  console.log('👀 Starting file watchers...');
  
  // Watch participant files
  if (fs.existsSync(participantsPath)) {
    const participantWatcher = chokidar.watch(`${participantsPath}/**/*.json`, {
      ignored: /^\./, // ignore dotfiles
      persistent: true,
      ignoreInitial: true
    });

    participantWatcher.on('change', (filePath) => {
      try {
        const fileName = path.basename(filePath, '.json');
        const eventId = fileName;
        
        console.log(`📝 Participant file changed: ${eventId}`);
        
        // Read the updated participant data
        const participantData = JSON.parse(fs.readFileSync(filePath, 'utf8'));
        
        // Broadcast to event room
        io.to(`event-${eventId}`).emit('participants-file-updated', {
          eventId,
          participants: participantData,
          timestamp: new Date()
        });
        
      } catch (error) {
        console.error('Error processing participant file change:', error);
      }
    });
    
    console.log(`✅ Watching participant files: ${participantsPath}`);
  } else {
    console.log(`⚠️ Participants directory not found: ${participantsPath}`);
  }

  // Watch event files
  if (fs.existsSync(eventsPath)) {
    const eventWatcher = chokidar.watch(`${eventsPath}/**/*.json`, {
      ignored: /^\./, // ignore dotfiles
      persistent: true,
      ignoreInitial: true
    });

    eventWatcher.on('change', (filePath) => {
      try {
        const fileName = path.basename(filePath, '.json');
        const eventId = fileName.replace(/\/meta$/, ''); // Remove /meta suffix if present
        
        console.log(`📝 Event file changed: ${eventId}`);
        
        // Read the updated event data
        const eventData = JSON.parse(fs.readFileSync(filePath, 'utf8'));
        
        // Broadcast to event room
        io.to(`event-${eventId}`).emit('event-data-updated', {
          eventId,
          eventData,
          timestamp: new Date()
        });
        
      } catch (error) {
        console.error('Error processing event file change:', error);
      }
    });
    
    console.log(`✅ Watching event files: ${eventsPath}`);
  } else {
    console.log(`⚠️ Events directory not found: ${eventsPath}`);
  }

  console.log('✅ File watchers started successfully');
}

module.exports = {
  startWatching
}; 