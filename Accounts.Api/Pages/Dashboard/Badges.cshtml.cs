using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;

namespace Accounts.Api.Pages.Dashboard;

public class BadgesModel : PageModel
{
    private readonly AccountsDbContext _db;

    public BadgesModel(AccountsDbContext db)
    {
        _db = db;
    }

    public List<Accounts.Data.Models.Events> AttendedEvents { get; set; } = new();
    public int EventsThisYear { get; set; }

    public async Task OnGetAsync(string? uid)
    {
        var user = await _db.UserCredentials.FirstOrDefaultAsync(u => u.Uid == uid) ?? await _db.UserCredentials.FirstOrDefaultAsync();
        if (user == null) return;

        AttendedEvents = await _db.EventParticipants
            .Where(p => p.Email == user.Email && p.AttendanceStatus == 3)
            .Join(_db.Events, p => p.EventId.ToString(), e => e.Eventid, (p, e) => e)
            .Distinct()
            .OrderByDescending(e => e.Startdate)
            .ToListAsync();

        var currentYear = DateTime.UtcNow.Year;
        EventsThisYear = AttendedEvents.Count(e => e.Startdate?.Year == currentYear);
    }
} 