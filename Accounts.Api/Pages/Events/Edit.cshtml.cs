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

    private Accounts.Data.Models.Events? _eventEntity;

    public async Task<IActionResult> OnGetAsync()
    {
        if (string.IsNullOrWhiteSpace(Id)) return NotFound();
        _eventEntity = await _db.Events.FirstOrDefaultAsync(e => e.Eventid == Id);
        if (_eventEntity == null) return NotFound();

        Input.EventName = _eventEntity.Eventname ?? string.Empty;
        Input.StartDate = _eventEntity.Startdate ?? DateTime.UtcNow;
        Input.EndDate = _eventEntity.Enddate ?? DateTime.UtcNow.AddHours(1);
        Input.Location = _eventEntity.Location ?? string.Empty;
        Input.ShortInfo = _eventEntity.Eventshortinfo;
        Input.LongInfo = _eventEntity.Eventinfopath; // placeholder, could load content
        return Page();
    }

    public async Task<IActionResult> OnPostAsync()
    {
        if (string.IsNullOrWhiteSpace(Id)) return NotFound();
        if (!ModelState.IsValid) return Page();
        var ev = await _db.Events.FirstOrDefaultAsync(e => e.Eventid == Id);
        if (ev == null) return NotFound();

        ev.Eventname = Input.EventName;
        ev.Startdate = Input.StartDate;
        ev.Enddate = Input.EndDate;
        ev.Location = Input.Location;
        ev.Eventshortinfo = Input.ShortInfo;
        ev.Eventinfopath = Input.LongInfo; // placeholder
        await _db.SaveChangesAsync();
        Success = "Event updated";
        return RedirectToPage("/Dashboard/Index");
    }

    public async Task<IActionResult> OnPostDeleteAsync()
    {
        if (string.IsNullOrWhiteSpace(Id)) return NotFound();
        var ev = await _db.Events.FirstOrDefaultAsync(e => e.Eventid == Id);
        if (ev == null) return NotFound();
        _db.Events.Remove(ev);
        await _db.SaveChangesAsync();
        return RedirectToPage("/Dashboard/Index");
    }

    public class EventInput
    {
        [Required] public string EventName { get; set; } = string.Empty;
        [Required] public DateTime StartDate { get; set; }
        [Required] public DateTime EndDate { get; set; }
        [Required] public string Location { get; set; } = string.Empty;
        public string? ShortInfo { get; set; }
        public string? LongInfo { get; set; }
    }
} 