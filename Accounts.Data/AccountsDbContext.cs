using Microsoft.EntityFrameworkCore;

namespace Accounts.Data;

public class AccountsDbContext : DbContext
{
    public AccountsDbContext(DbContextOptions<AccountsDbContext> options) : base(options)
    {
    }

    // TODO: Add DbSet<TEntity> properties once the schema is scaffolded.

    public DbSet<Models.UserCredentials> UserCredentials { get; set; } = null!;
} 