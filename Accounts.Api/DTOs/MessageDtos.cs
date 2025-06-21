namespace Accounts.Api.DTOs;

public record MessageCreateRequest(string SenderUid, string Body);
public record MessageDto(ulong MsgId, Guid ChannelId, string SenderUid, string Body, DateTime CreatedAt); 