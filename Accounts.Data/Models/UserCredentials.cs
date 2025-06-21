using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Accounts.Data.Models;

[Table("user_credentials")]
public class UserCredentials
{
    [Key]
    [Column("uid", TypeName="char(36)")]
    public string Uid { get; set; } = Guid.NewGuid().ToString();

    [Column("email")]
    [MaxLength(255)]
    public string Email { get; set; } = string.Empty;

    [Column("password")]
    [MaxLength(255)]
    public string PasswordHash { get; set; } = string.Empty;

    [Column("is_student")]
    public bool IsStudent { get; set; }

    [Column("user_level")]
    public int UserLevel { get; set; }

    [Column("created_at")]
    public DateTime CreatedAt { get; set; } = DateTime.UtcNow;
} 