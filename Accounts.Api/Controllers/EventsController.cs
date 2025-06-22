using Accounts.Api.DTOs;
using Accounts.Data.Models;
using Accounts.Data.FileStorage;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace Accounts.Api.Controllers;

[ApiController]
[Route("api/[controller]")]
public class EventsController : ControllerBase
{
    private readonly AccountsDbContext _db;
    private readonly FileStorage _files;

    public EventsController(AccountsDbContext db, FileStorage files)
    {
        _db = db;
        _files = files;
    }

    // GET api/events
    [HttpGet]
    public async Task<IEnumerable<EventSummary>> GetUpcoming()
    {
        var now = DateTime.UtcNow;
        return await _db.Events
            .Where(e => e.Startdate >= now)
            .OrderBy(e => e.Startdate)
            .Select(e => new EventSummary(e.Eventid, e.Eventname ?? string.Empty, e.Startdate ?? DateTime.MinValue, e.Enddate ?? DateTime.MinValue, e.Location ?? string.Empty, e.Participantcount))
            .ToListAsync();
    }

    // GET api/events/{id}
    [HttpGet("{id}")]
    public async Task<IActionResult> GetById(string id)
    {
        var evt = await _db.Events.FirstOrDefaultAsync(e => e.Eventid == id);
        if (evt == null) return NotFound();

        var participants = await _db.EventParticipants.Where(p => p.EventId.ToString() == id).ToListAsync();
        return Ok(new
        {
            Event = evt,
            Participants = participants
        });
    }

    // POST api/events
    [HttpPost]
    public async Task<ActionResult<EventSummary>> Create(EventCreateRequest req)
    {
        var evt = new Events
        {
            Eventid = Guid.NewGuid().ToString(),
            Eventname = req.EventName,
            Startdate = req.StartDate,
            Enddate = req.EndDate,
            Location = req.Location,
            Eventshortinfo = req.ShortInfo,
            Eventcreator = req.EventCreatorUid,
            Eventkey = GenerateEventKey(),
            Participantcount = 0
        };
        _db.Events.Add(evt);
        await _db.SaveChangesAsync();

        // file meta
        _files.SaveEventData(evt.Eventid, new { evt.Eventname, evt.Eventshortinfo, evt.Startdate, evt.Enddate, evt.Location });

        var summary = new EventSummary(evt.Eventid, evt.Eventname ?? string.Empty, evt.Startdate ?? DateTime.MinValue, evt.Enddate ?? DateTime.MinValue, evt.Location ?? string.Empty, 0);
        return CreatedAtAction(nameof(GetById), new { id = evt.Eventid }, summary);
    }

    // POST api/events/{id}/participants
    [HttpPost("{id}/participants")]
    public async Task<IActionResult> AddParticipant(string id, ParticipantAddRequest req)
    {
        var evt = await _db.Events.FindAsync(id);
        if (evt == null) return NotFound();
        if (req.Uid == null && req.Email == null) return BadRequest();

        var participant = new EventParticipants
        {
            ParticipantId = Guid.NewGuid(),
            EventId = Guid.Parse(id),
            Uid = req.Uid != null ? Guid.Parse(req.Uid) : null,
            Email = req.Email ?? string.Empty,
            JoinedAt = DateTime.UtcNow,
            Registered = req.Uid != null,
            AttendanceStatus = req.Uid != null ? 1 : 0 // Pending if registered user, Invited if email only
        };
        _db.EventParticipants.Add(participant);
        evt.Participantcount += 1;
        await _db.SaveChangesAsync();

        // update file
        _files.AddParticipant(id, participant.Uid?.ToString() ?? participant.ParticipantId.ToString(), new Dictionary<string, string>
        {
            ["email"] = participant.Email,
            ["status"] = "registered"
        });

        return Ok();
    }

    // DELETE api/events/{id}
    [HttpDelete("{id}")]
    public async Task<IActionResult> Delete(string id)
    {
        var evt = await _db.Events.FindAsync(id);
        if (evt == null) return NotFound();
        _db.Events.Remove(evt);
        await _db.SaveChangesAsync();

        _files.DeleteEventData(id);
        return NoContent();
    }

    private static string GenerateEventKey() => Convert.ToBase64String(Guid.NewGuid().ToByteArray()).Replace("=","" ).Replace("+","");
} 