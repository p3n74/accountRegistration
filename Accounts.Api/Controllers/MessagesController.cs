using Accounts.Api.DTOs;
using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace Accounts.Api.Controllers;

[ApiController]
[Route("api/channels/{channelId}/messages")]
public class MessagesController : ControllerBase
{
    private readonly AccountsDbContext _db;
    public MessagesController(AccountsDbContext db) { _db = db; }

    [HttpGet]
    public async Task<IEnumerable<MessageDto>> List(Guid channelId, [FromQuery] ulong? after = null)
    {
        var query = _db.Messages.Where(m => m.ChannelId == channelId);
        if (after.HasValue) query = query.Where(m => m.MsgId > after.Value);
        return await query.OrderBy(m => m.CreatedAt)
            .Select(m => new MessageDto(m.MsgId, m.ChannelId, m.SenderUid != null ? m.SenderUid.ToString() : string.Empty, m.Body ?? string.Empty, m.CreatedAt))
            .ToListAsync();
    }

    [HttpPost]
    public async Task<ActionResult<MessageDto>> Create(Guid channelId, MessageCreateRequest req)
    {
        var msg = new Messages
        {
            ChannelId = channelId,
            SenderUid = Guid.Parse(req.SenderUid),
            Body = req.Body,
            CreatedAt = DateTime.UtcNow
        };
        _db.Messages.Add(msg);
        await _db.SaveChangesAsync();
        return CreatedAtAction(nameof(List), new { channelId }, new MessageDto(msg.MsgId, msg.ChannelId, msg.SenderUid?.ToString() ?? string.Empty, msg.Body ?? string.Empty, msg.CreatedAt));
    }
} 