using Accounts.Data.Models;
using EventEntityModel = Accounts.Data.Models.Events;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations;

namespace Accounts.Api.Pages.Events;

public class RegisterModel : PageModel
{
    private readonly AccountsDbContext _db;
    public RegisterModel(AccountsDbContext db) => _db = db;

    [FromRoute]
    public string? Key { get; set; }

    public EventEntityModel? EventEntity { get; set; }

    [BindProperty]
    [EmailAddress]
    [Required]
    public string? InputEmail { get; set; }

    public string? Error { get; set; }
    public string? Success { get; set; }

    public async Task<IActionResult> OnGetAsync()
    {
        if (string.IsNullOrWhiteSpace(Key)) return NotFound();
        EventEntity = await _db.Events.FirstOrDefaultAsync(e => e.Eventkey == Key);
        if (EventEntity == null) return NotFound();
        return Page();
    }

    public async Task<IActionResult> OnPostAsync()
    {
        if (string.IsNullOrWhiteSpace(Key)) return NotFound();
        EventEntity = await _db.Events.FirstOrDefaultAsync(e => e.Eventkey == Key);
        if (EventEntity == null) return NotFound();
        if (!ModelState.IsValid) return Page();

        // Check duplicate
        var existing = await _db.EventParticipants.AnyAsync(p => p.EventId == Guid.Parse(EventEntity.Eventid) && p.Email == InputEmail);
        if (existing)
        {
            Error = "You are already registered for this event.";
            return Page();
        }

        var participant = new EventParticipants
        {
            ParticipantId = Guid.NewGuid(),
            EventId = Guid.Parse(EventEntity.Eventid),
            Email = InputEmail!,
            JoinedAt = DateTime.UtcNow,
            Registered = false,
            AttendanceStatus = 1 // Pending registration
        };
        _db.EventParticipants.Add(participant);
        EventEntity.Participantcount += 1;
        await _db.SaveChangesAsync();
        Success = "Successfully registered!";
        return Page();
    }
} 