namespace Accounts.Api.DTOs;

public record RegisterRequest(string Email, string Password, bool IsStudent = true);
public record LoginRequest(string Email, string Password);

public record AuthResponse(string Uid, string Email, int UserLevel, string Token); 