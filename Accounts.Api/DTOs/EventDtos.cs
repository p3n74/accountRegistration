namespace Accounts.Api.DTOs;

public record EventCreateRequest(string EventName, DateTime StartDate, DateTime EndDate, string Location, string ShortInfo, string EventCreatorUid);
public record EventSummary(string EventId, string EventName, DateTime StartDate, DateTime EndDate, string Location, int ParticipantCount);
public record ParticipantAddRequest(string? Uid, string? Email); 