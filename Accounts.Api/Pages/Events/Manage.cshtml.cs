using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations;

namespace Accounts.Api.Pages.Events;

public class ManageModel : PageModel
{
    private readonly AccountsDbContext _db;
    public ManageModel(AccountsDbContext db) => _db = db;

    [FromRoute] public string? Id { get; set; }

    public Accounts.Data.Models.Events? EventEntity { get; set; }
    public List<EventParticipants> Participants { get; set; } = new();

    [BindProperty]
    [EmailAddress]
    public string? NewEmail { get; set; }

    public async Task<IActionResult> OnGetAsync()
    {
        if (!await LoadAsync()) return NotFound();
        return Page();
    }

    public async Task<IActionResult> OnPostAddAsync()
    {
        if (!await LoadAsync()) return NotFound();
        if (string.IsNullOrWhiteSpace(NewEmail))
        {
            ModelState.AddModelError("NewEmail", "Email required");
            return Page();
        }
        var exists = Participants.Any(p => p.Email == NewEmail);
        if (exists)
        {
            ModelState.AddModelError("NewEmail", "Already added");
            return Page();
        }
        var part = new EventParticipants
        {
            ParticipantId = Guid.NewGuid(),
            EventId = Guid.Parse(Id!),
            Email = NewEmail,
            JoinedAt = DateTime.UtcNow,
            AttendanceStatus = false
        };
        _db.EventParticipants.Add(part);
        await _db.SaveChangesAsync();
        return RedirectToPage(new { id = Id });
    }

    public async Task<IActionResult> OnPostMarkAsync(Guid participantId)
    {
        var pp = await _db.EventParticipants.FirstOrDefaultAsync(p => p.ParticipantId == participantId);
        if (pp != null)
        {
            pp.AttendanceStatus = true;
            await _db.SaveChangesAsync();
        }
        return RedirectToPage(new { id = Id });
    }

    public async Task<IActionResult> OnPostRemoveAsync(Guid participantId)
    {
        var pp = await _db.EventParticipants.FirstOrDefaultAsync(p => p.ParticipantId == participantId);
        if (pp != null)
        {
            _db.EventParticipants.Remove(pp);
            await _db.SaveChangesAsync();
        }
        return RedirectToPage(new { id = Id });
    }

    private async Task<bool> LoadAsync()
    {
        if (string.IsNullOrWhiteSpace(Id)) return false;
        EventEntity = await _db.Events.FirstOrDefaultAsync(e => e.Eventid == Id);
        if (EventEntity == null) return false;
        Participants = await _db.EventParticipants.Where(p => p.EventId == Guid.Parse(Id)).OrderByDescending(p => p.JoinedAt).ToListAsync();
        return true;
    }
} 