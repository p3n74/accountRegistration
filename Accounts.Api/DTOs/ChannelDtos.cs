namespace Accounts.Api.DTOs;

public record ChannelCreateRequest(string ChannelType, string? RelatedId, string Name, string CreatedByUid);
public record ChannelSummary(string ChannelId, string ChannelType, string Name, string? RelatedId);
public record ChannelMemberAddRequest(string Uid, string Role); 