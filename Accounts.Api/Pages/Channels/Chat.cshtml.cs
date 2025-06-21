using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations;

namespace Accounts.Api.Pages.Channels;

public class ChatModel : PageModel
{
    private readonly AccountsDbContext _db;
    public ChatModel(AccountsDbContext db) => _db = db;

    [FromRoute] public Guid id { get; set; }

    public Accounts.Data.Models.Channels? Channel { get; set; }
    public List<Accounts.Data.Models.Messages> Messages { get; set; } = new();

    [BindProperty]
    [Required]
    public string NewMessage { get; set; } = string.Empty;

    private Guid _currentUid => _db.UserCredentials.Select(u => Guid.Parse(u.Uid)).First(); // placeholder

    public async Task<IActionResult> OnGetAsync()
    {
        await LoadAsync();
        return Page();
    }

    public async Task<IActionResult> OnPostAsync()
    {
        if (!ModelState.IsValid) { await LoadAsync(); return Page(); }
        var existsMember = await _db.ChannelMembers.AnyAsync(cm => cm.ChannelId == id && cm.Uid == _currentUid);
        if (!existsMember)
        {
            _db.ChannelMembers.Add(new ChannelMembers { ChannelId = id, Uid = _currentUid, Role = "member" });
        }
        _db.Messages.Add(new Messages { ChannelId = id, SenderUid = _currentUid, Body = NewMessage, CreatedAt = DateTime.UtcNow });
        await _db.SaveChangesAsync();
        return RedirectToPage(new { id });
    }

    private async Task LoadAsync()
    {
        Channel = await _db.Channels.FirstOrDefaultAsync(c => c.ChannelId == id);
        Messages = await _db.Messages.Where(m => m.ChannelId == id).OrderBy(m => m.CreatedAt).Take(100).ToListAsync();
    }
} 