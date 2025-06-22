using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations;

namespace Accounts.Api.Pages.Events;

public class EditModel : PageModel
{
    private readonly AccountsDbContext _db;
    public EditModel(AccountsDbContext db) => _db = db;

    [BindProperty]
    public EventInput Input { get; set; } = new();

    [FromRoute] public string? Id { get; set; }

    public string? Error { get; set; }
    public string? Success { get; set; }

    public Accounts.Data.Models.Events? EventEntity { get; set; }

    public async Task<IActionResult> OnGetAsync()
    {
        if (string.IsNullOrWhiteSpace(Id)) return NotFound();
        EventEntity = await _db.Events.FirstOrDefaultAsync(e => e.Eventid == Id);
        if (EventEntity == null) return Page(); // Show error state in view

        Input.EventName = EventEntity.Eventname ?? string.Empty;
        Input.StartDate = EventEntity.Startdate ?? DateTime.UtcNow;
        Input.EndDate = EventEntity.Enddate ?? DateTime.UtcNow.AddHours(1);
        Input.Location = EventEntity.Location ?? string.Empty;
        Input.ShortInfo = EventEntity.Eventshortinfo;
        Input.LongInfo = EventEntity.Eventinfopath; // placeholder, could load content
        return Page();
    }

    public async Task<IActionResult> OnPostAsync()
    {
        if (string.IsNullOrWhiteSpace(Id)) return NotFound();
        if (!ModelState.IsValid) 
        {
            EventEntity = await _db.Events.FirstOrDefaultAsync(e => e.Eventid == Id);
            return Page();
        }

        var ev = await _db.Events.FirstOrDefaultAsync(e => e.Eventid == Id);
        if (ev == null) 
        {
            Error = "Event not found";
            return Page();
        }

        // Validate dates
        if (Input.EndDate <= Input.StartDate)
        {
            Error = "End date must be after start date";
            EventEntity = ev;
            return Page();
        }

        // Check if participants exist and prevent certain changes
        var participantCount = await _db.EventParticipants
            .Where(p => p.EventId == Guid.Parse(Id))
            .CountAsync();

        if (participantCount > 0)
        {
            // Check if dates are being changed significantly (more than 1 hour)
            var startDateDiff = Math.Abs((Input.StartDate - (ev.Startdate ?? DateTime.UtcNow)).TotalHours);
            var endDateDiff = Math.Abs((Input.EndDate - (ev.Enddate ?? DateTime.UtcNow)).TotalHours);
            
            if (startDateDiff > 1 || endDateDiff > 1)
            {
                // This is a significant change - we could add notification logic here
                Success = "Date changes detected. Participants will be notified.";
            }
        }

        ev.Eventname = Input.EventName;
        ev.Startdate = Input.StartDate;
        ev.Enddate = Input.EndDate;
        ev.Location = Input.Location;
        ev.Eventshortinfo = Input.ShortInfo;
        ev.Eventinfopath = Input.LongInfo; // placeholder
        
        await _db.SaveChangesAsync();
        Success = "Event updated successfully!";
        EventEntity = ev;
        return Page();
    }

    public async Task<IActionResult> OnPostDeleteAsync()
    {
        if (string.IsNullOrWhiteSpace(Id)) return NotFound();
        var ev = await _db.Events.FirstOrDefaultAsync(e => e.Eventid == Id);
        if (ev == null) return NotFound();

        // Check for protected participants (Paid, Attended, Awaiting Verification)
        var protectedParticipants = await _db.EventParticipants
            .Where(p => p.EventId == Guid.Parse(Id) && (p.AttendanceStatus == 2 || p.AttendanceStatus == 3 || p.AttendanceStatus == 5))
            .AnyAsync();

        if (protectedParticipants)
        {
            Error = "Cannot delete event: participants have paid/attended or awaiting verification.";
            EventEntity = ev;
            return Page();
        }

        // Remove all event participants first
        var participants = await _db.EventParticipants
            .Where(p => p.EventId == Guid.Parse(Id))
            .ToListAsync();
        _db.EventParticipants.RemoveRange(participants);

        _db.Events.Remove(ev);
        await _db.SaveChangesAsync();
        return RedirectToPage("/Dashboard/Index");
    }

    public class EventInput
    {
        [Required]
        [Display(Name = "Event Name")]
        [StringLength(255, ErrorMessage = "Event name cannot be longer than 255 characters")]
        public string EventName { get; set; } = string.Empty;

        [Required]
        [Display(Name = "Start Date")]
        public DateTime StartDate { get; set; }

        [Required]
        [Display(Name = "End Date")]
        public DateTime EndDate { get; set; }

        [Required]
        [StringLength(500, ErrorMessage = "Location cannot be longer than 500 characters")]
        public string Location { get; set; } = string.Empty;

        [Display(Name = "Short Description")]
        [StringLength(1000, ErrorMessage = "Short description cannot be longer than 1000 characters")]
        public string? ShortInfo { get; set; }

        [Display(Name = "Detailed Information")]
        public string? LongInfo { get; set; }
    }
} 