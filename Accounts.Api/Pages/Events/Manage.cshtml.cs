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
        if (!await LoadAsync()) return Page(); // Show error state in view instead of NotFound
        return Page();
    }

    public async Task<IActionResult> OnPostAddAsync()
    {
        if (!await LoadAsync()) return Page();
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
            AttendanceStatus = 0, // Invited
            Registered = false
        };
        _db.EventParticipants.Add(part);
        
        // Update participant count
        if (EventEntity != null)
        {
            EventEntity.Participantcount = EventEntity.Participantcount + 1;
        }
        
        await _db.SaveChangesAsync();
        return RedirectToPage(new { id = Id });
    }

    public async Task<IActionResult> OnPostUpdateStatusAsync(Guid participantId, int newStatus)
    {
        var pp = await _db.EventParticipants.FirstOrDefaultAsync(p => p.ParticipantId == participantId);
        if (pp != null)
        {
            pp.AttendanceStatus = newStatus;
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
            
            // Update participant count
            if (EventEntity != null)
            {
                EventEntity.Participantcount = Math.Max(0, EventEntity.Participantcount - 1);
            }
            
            await _db.SaveChangesAsync();
        }
        return RedirectToPage(new { id = Id });
    }

    private async Task<bool> LoadAsync()
    {
        if (string.IsNullOrWhiteSpace(Id)) return false;
        
        EventEntity = await _db.Events.FirstOrDefaultAsync(e => e.Eventid == Id);
        if (EventEntity == null) return false;
        
        Participants = await _db.EventParticipants
            .Where(p => p.EventId == Guid.Parse(Id))
            .OrderByDescending(p => p.JoinedAt)
            .ToListAsync();
        
        return true;
    }

    public (string Label, string CssClass) GetStatusInfo(int status)
    {
        return status switch
        {
            0 => ("Invited", "bg-yellow-100 text-yellow-800 border-yellow-200"),
            1 => ("Pending", "bg-orange-100 text-orange-800 border-orange-200"),
            2 => ("Paid", "bg-green-100 text-green-800 border-green-200"),
            3 => ("Attended", "bg-emerald-100 text-emerald-800 border-emerald-200"),
            4 => ("Absent", "bg-red-100 text-red-800 border-red-200"),
            5 => ("Awaiting Verification", "bg-purple-100 text-purple-800 border-purple-200"),
            _ => ("Unknown", "bg-gray-100 text-gray-600 border-gray-200")
        };
    }
} 