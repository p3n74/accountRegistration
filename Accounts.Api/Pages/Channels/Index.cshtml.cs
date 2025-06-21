using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;

namespace Accounts.Api.Pages.Channels;

public class IndexModel : PageModel
{
    private readonly AccountsDbContext _db;
    public IndexModel(AccountsDbContext db) { _db = db; }

    public List<Accounts.Data.Models.Channels> Channels { get; set; } = new();

    public async Task OnGetAsync(string? uid)
    {
        // placeholder user selection
        var userId = uid ?? (await _db.UserCredentials.Select(u => u.Uid).FirstOrDefaultAsync());
        if (userId == null) return;
        var guidUid = Guid.Parse(userId);
        Channels = await _db.Channels
            .Where(c => _db.ChannelMembers.Any(cm => cm.ChannelId == c.ChannelId && cm.Uid == guidUid))
            .OrderByDescending(c => c.CreatedAt)
            .ToListAsync();
    }
} 