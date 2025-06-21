using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations;

namespace Accounts.Api.Pages.Events;

public class CreateModel : PageModel
{
    private readonly AccountsDbContext _db;
    public CreateModel(AccountsDbContext db) { _db = db; }

    [BindProperty] public EventInput Input { get; set; } = new();
    public string? Error { get; set; }
    public string? Success { get; set; }

    public void OnGet() { }

    public async Task<IActionResult> OnPostAsync(string? uid)
    {
        if (!ModelState.IsValid) return Page();

        var creator = await _db.UserCredentials.FirstOrDefaultAsync(u => u.Uid == uid) ?? await _db.UserCredentials.FirstOrDefaultAsync();
        if (creator == null) { Error = "User not found"; return Page(); }

        var ev = new Accounts.Data.Models.Events
        {
            Eventid = Guid.NewGuid().ToString("N"),
            Eventname = Input.EventName,
            Startdate = Input.StartDate,
            Enddate = Input.EndDate,
            Location = Input.Location,
            Eventshortinfo = Input.ShortInfo,
            Eventinfopath = null, // could store full info via FileStorage later
            Eventcreator = creator.Uid,
            EventcreatorNavigation = creator,
            Participantcount = 0,
            Views = 0
        };
        _db.Events.Add(ev);
        await _db.SaveChangesAsync();
        Success = "Event created";
        return RedirectToPage("/Dashboard/Index");
    }

    public class EventInput
    {
        [Required]
        [Display(Name="Event Name")]
        public string EventName { get; set; } = string.Empty;

        [Required]
        [Display(Name="Start Date")]
        public DateTime StartDate { get; set; }

        [Required]
        [Display(Name="End Date")]
        public DateTime EndDate { get; set; }

        [Required]
        public string Location { get; set; } = string.Empty;
        [Display(Name="Short Description")]
        public string? ShortInfo { get; set; }
        [Display(Name="Detailed Information")]
        public string? LongInfo { get; set; }
    }
} 