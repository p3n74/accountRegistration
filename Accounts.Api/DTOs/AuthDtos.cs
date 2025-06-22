namespace Accounts.Api.DTOs;

public record RegisterRequest(
    string Email,
    string Password,
    string FirstName,
    string? MiddleName,
    string LastName,
    int? ProgramId = null
);
public record LoginRequest(string Email, string Password);

public record AuthResponse(string Uid, string Email, int UserLevel, string Token);

public record CheckStudentRequest(string Email);
public record CheckStudentResponse(bool Exists, string? FirstName = null, string? MiddleName = null, string? LastName = null, int? ProgramId = null); 