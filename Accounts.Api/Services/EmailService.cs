using MailKit.Net.Smtp;
using MailKit.Security;
using MimeKit;

namespace Accounts.Api.Services;

public class EmailService
{
    private readonly string _smtpHost = "smtp.gmail.com";
    private readonly int _smtpPort = 587;
    private readonly string _fromAddress = "21102134@usc.edu.ph";
    private readonly string _fromName = "DCISM Accounts";
    private readonly string _password;
    private readonly string _baseUrl;
    private const string LogoUrl = "https://ismis.dcism.org/assets/img/devteamlogo.png"; // TODO change if needed

    private const string SignatureTemplate = @"<div style=""margin-top: 20px; padding-top: 15px; border-top: 2px solid #e0e0e0; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.4; color: #333;"">
        <table cellpadding=""0"" cellspacing=""0"" border=""0"" style=""width: 100%;"">
            <tr>
                <td style=""vertical-align: top; padding-right: 15px; width: 135px;"">
                    <img src=""{0}"" alt=""University of San Carlos Logo"" style=""width: 120px; height: 120px; border-radius: 8px; display: block;"" />
                </td>
                <td style=""vertical-align: top;"">
                    <div style=""margin-bottom: 15px;"">
                        <div style=""font-weight: bold; font-size: 16px; color: #2c5530; margin-bottom: 5px;"">
                            Nikolai Tristan E. Pazon
                        </div>
                        <div style=""color: #666; margin-bottom: 3px;"">
                            Vice-President for Finance
                        </div>
                        <div style=""color: #666; margin-bottom: 8px;"">
                            Computer and Information Sciences Council
                        </div>
                        <div style=""margin-bottom: 3px;"">
                            <a href=""http://dcism.org"" style=""color: #2c5530; text-decoration: none;"">
                                Department of Computer, Information Sciences, and Mathematics
                            </a>
                        </div>
                        <div style=""font-weight: bold; color: #2c5530; font-size: 15px;"">
                            UNIVERSITY OF SAN CARLOS
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div style=""font-size: 11px; color: #888; font-style: italic; padding-top: 10px; border-top: 1px solid #f0f0f0;"">
            <strong>CONFIDENTIAL:</strong> This email and any attachments are confidential and intended solely for the addressee.
            If you are not the intended recipient, please delete this email and notify the sender immediately.
            Unauthorized disclosure, copying, or distribution is strictly prohibited.
        </div>
    </div>";

    private static readonly string SignatureHtml = string.Format(SignatureTemplate, LogoUrl);

    public EmailService(IConfiguration config, IWebHostEnvironment env)
    {
        // Try env var first, else read apikey file
        _password = Environment.GetEnvironmentVariable("GMAIL_APIKEY") ?? ReadKeyFromFile();
        if (string.IsNullOrWhiteSpace(_password))
        {
            throw new InvalidOperationException("SMTP password not configured. Set GMAIL_APIKEY env var or apikey file.");
        }

        _baseUrl = env.IsDevelopment() ? "http://localhost:5015" : (config["App:BaseUrl"] ?? "https://example.com");
    }

    private static string ReadKeyFromFile()
    {
        try
        {
            var path = Path.Combine(AppContext.BaseDirectory, "../../../apikey");
            if (!File.Exists(path)) return string.Empty;
            var content = File.ReadAllText(path).Trim();
            if (content.StartsWith("$apikey"))
            {
                var idx = content.IndexOf('"');
                var last = content.LastIndexOf('"');
                if (idx >= 0 && last > idx) return content.Substring(idx + 1, last - idx - 1);
            }
            return content;
        }
        catch { return string.Empty; }
    }

    public async Task SendVerificationEmailAsync(string toEmail, string token)
    {
        var message = new MimeMessage();
        message.From.Add(new MailboxAddress(_fromName, _fromAddress));
        message.To.Add(MailboxAddress.Parse(toEmail));
        message.Subject = "Email Verification - DCISM Accounts";

        var builder = new BodyBuilder
        {
            HtmlBody = $@"<p>Welcome to DCISM Accounts!</p>
                          <p>Please click the link below to verify your email address:</p>
                          <p><a href='{_baseUrl}/auth/verify/{token}'>Verify My Email</a></p>
                          <p>If you didn't create an account, you can safely ignore this email.</p>
                          {SignatureHtml}"
        };
        message.Body = builder.ToMessageBody();
        await SendAsync(message);
    }

    public async Task SendPasswordResetEmailAsync(string toEmail, string token)
    {
        var message = new MimeMessage();
        message.From.Add(new MailboxAddress(_fromName, _fromAddress));
        message.To.Add(MailboxAddress.Parse(toEmail));
        message.Subject = "Password Reset Request";

        var builder = new BodyBuilder
        {
            HtmlBody = $@"<p>You requested a password reset. Click the link below to reset your password:</p>
                          <p><a href='{_baseUrl}/auth/reset/{token}'>Reset My Password</a></p>
                          <p>This link will expire in 1 hour.</p>
                          {SignatureHtml}"
        };
        message.Body = builder.ToMessageBody();
        await SendAsync(message);
    }

    private async Task SendAsync(MimeMessage message)
    {
        using var client = new SmtpClient();
        await client.ConnectAsync(_smtpHost, _smtpPort, SecureSocketOptions.StartTls);
        await client.AuthenticateAsync(_fromAddress, _password);
        await client.SendAsync(message);
        await client.DisconnectAsync(true);
    }
} 