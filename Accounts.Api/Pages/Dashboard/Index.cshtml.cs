using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;

namespace Accounts.Api.Pages.Dashboard;

public class IndexModel : PageModel
{
    private readonly AccountsDbContext _db;

    public IndexModel(AccountsDbContext db)
    {
        _db = db;
    }

    public UserCredentials? UserInfo { get; set; }
    public List<Accounts.Data.Models.Events> CreatedEvents { get; set; } = new();
    public List<Accounts.Data.Models.Events> AttendedEvents { get; set; } = new();

    public int AttendedCount => AttendedEvents.Count;
    public int BadgesCount { get; set; } = 0; // placeholder until badge system exists

    public async Task OnGetAsync(string? uid)
    {
        // TODO: Replace with real authentication
        if (string.IsNullOrWhiteSpace(uid))
        {
            UserInfo = await _db.UserCredentials.FirstOrDefaultAsync();
        }
        else
        {
            UserInfo = await _db.UserCredentials.FirstOrDefaultAsync(u => u.Uid == uid);
            if (UserInfo == null)
            {
                UserInfo = await _db.UserCredentials.FirstOrDefaultAsync();
            }
        }

        if (UserInfo == null) return;

        CreatedEvents = await _db.Set<Accounts.Data.Models.Events>()
            .Where(e => e.Eventcreator == UserInfo.Uid)
            .OrderByDescending(e => e.Startdate)
            .Take(10)
            .ToListAsync();

        // Simplified attended events logic – match by email if GUID mismatch
        AttendedEvents = await _db.EventParticipants
            .Where(p => p.Email == UserInfo.Email && p.AttendanceStatus)
            .Join(_db.Events, p => p.EventId.ToString(), e => e.Eventid, (p, e) => e)
            .Distinct()
            .OrderByDescending(e => e.Startdate)
            .Take(10)
            .ToListAsync();

        BadgesCount = AttendedEvents.Count(e => !string.IsNullOrWhiteSpace(e.Eventbadgepath));
    }
} 