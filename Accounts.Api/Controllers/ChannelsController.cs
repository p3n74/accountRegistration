using Accounts.Api.DTOs;
using Accounts.Data.FileStorage;
using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace Accounts.Api.Controllers;

[ApiController]
[Route("api/[controller]")]
public class ChannelsController : ControllerBase
{
    private readonly AccountsDbContext _db;
    private readonly FileStorage _files;

    public ChannelsController(AccountsDbContext db, FileStorage files)
    {
        _db = db;
        _files = files;
    }

    [HttpGet]
    public async Task<IEnumerable<ChannelSummary>> GetUserChannels([FromQuery] Guid uid)
    {
        return await _db.Channels
            .Where(c => _db.ChannelMembers.Any(cm => cm.ChannelId == c.ChannelId && cm.Uid == uid))
            .OrderByDescending(c => c.CreatedAt)
            .Select(c => new ChannelSummary(c.ChannelId.ToString(), c.ChannelType, c.Name ?? string.Empty, c.RelatedId))
            .ToListAsync();
    }

    [HttpGet("{id}")]
    public async Task<IActionResult> GetById(Guid id)
    {
        var channel = await _db.Channels.Include(c => c.ChannelMembers).FirstOrDefaultAsync(c => c.ChannelId == id);
        if (channel == null) return NotFound();
        return Ok(channel);
    }

    [HttpPost]
    public async Task<ActionResult<ChannelSummary>> Create(ChannelCreateRequest req)
    {
        var channel = new Channels
        {
            ChannelType = req.ChannelType,
            RelatedId = req.RelatedId,
            Name = req.Name,
            CreatedBy = Guid.Parse(req.CreatedByUid)
        };
        _db.Channels.Add(channel);
        await _db.SaveChangesAsync();

        var adminMember = new ChannelMembers
        {
            ChannelId = channel.ChannelId,
            Uid = Guid.Parse(req.CreatedByUid),
            Role = "admin"
        };
        _db.ChannelMembers.Add(adminMember);
        await _db.SaveChangesAsync();

        return CreatedAtAction(nameof(GetById), new { id = channel.ChannelId }, new ChannelSummary(channel.ChannelId.ToString(), channel.ChannelType, channel.Name ?? string.Empty, channel.RelatedId));
    }

    [HttpPost("{id}/members")]
    public async Task<IActionResult> AddMember(Guid id, ChannelMemberAddRequest req)
    {
        var exists = await _db.ChannelMembers.AnyAsync(cm => cm.ChannelId == id && cm.Uid == Guid.Parse(req.Uid));
        if (exists) return Ok();
        _db.ChannelMembers.Add(new ChannelMembers { ChannelId = id, Uid = Guid.Parse(req.Uid), Role = req.Role });
        await _db.SaveChangesAsync();
        return Ok();
    }
} 