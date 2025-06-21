using System;
using System.Collections.Generic;
using Microsoft.EntityFrameworkCore;
using Pomelo.EntityFrameworkCore.MySql.Scaffolding.Internal;

namespace Accounts.Data.Models;

public partial class AccountsDbContext : DbContext
{
    public AccountsDbContext()
    {
    }

    public AccountsDbContext(DbContextOptions<AccountsDbContext> options)
        : base(options)
    {
    }

    public virtual DbSet<ChannelMembers> ChannelMembers { get; set; }

    public virtual DbSet<Channels> Channels { get; set; }

    public virtual DbSet<Messages> Messages { get; set; }

    public virtual DbSet<Notifications> Notifications { get; set; }

    public virtual DbSet<Events> Events { get; set; }

    public virtual DbSet<UserCredentials> UserCredentials { get; set; }

    protected override void OnConfiguring(DbContextOptionsBuilder optionsBuilder)
#warning To protect potentially sensitive information in your connection string, you should move it out of source code. You can avoid scaffolding the connection string by using the Name= syntax to read it from configuration - see https://go.microsoft.com/fwlink/?linkid=2131148. For more guidance on storing connection strings, see https://go.microsoft.com/fwlink/?LinkId=723263.
        => optionsBuilder.UseMySql("server=127.0.0.1;database=s21102134_palisade;user=s21102134_palisade;password=webwebwebweb", Microsoft.EntityFrameworkCore.ServerVersion.Parse("10.11.11-mariadb"));

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        modelBuilder
            .UseCollation("utf8mb4_general_ci")
            .HasCharSet("utf8mb4");

        modelBuilder.Entity<ChannelMembers>(entity =>
        {
            entity.HasKey(e => new { e.ChannelId, e.Uid })
                .HasName("PRIMARY")
                .HasAnnotation("MySql:IndexPrefixLength", new[] { 0, 0 });

            entity.ToTable("channel_members");

            entity.HasIndex(e => e.Uid, "idx_member_uid");

            entity.Property(e => e.ChannelId).HasColumnName("channel_id");
            entity.Property(e => e.Uid).HasColumnName("uid");
            entity.Property(e => e.JoinedAt)
                .HasDefaultValueSql("current_timestamp()")
                .HasColumnType("datetime")
                .HasColumnName("joined_at");
            entity.Property(e => e.Role)
                .HasDefaultValueSql("'member'")
                .HasColumnType("enum('member','admin')")
                .HasColumnName("role");

            entity.HasOne(d => d.Channel).WithMany(p => p.ChannelMembers)
                .HasForeignKey(d => d.ChannelId)
                .HasConstraintName("fk_cm_channel");
        });

        modelBuilder.Entity<Channels>(entity =>
        {
            entity.HasKey(e => e.ChannelId).HasName("PRIMARY");

            entity.ToTable("channels");

            entity.HasIndex(e => new { e.ChannelType, e.RelatedId }, "idx_channel_rel");

            entity.HasIndex(e => e.ChannelType, "idx_channel_type");

            entity.Property(e => e.ChannelId).HasColumnName("channel_id");
            entity.Property(e => e.ChannelType)
                .HasColumnType("enum('event','group','dm','system')")
                .HasColumnName("channel_type");
            entity.Property(e => e.CreatedAt)
                .HasDefaultValueSql("current_timestamp()")
                .HasColumnType("datetime")
                .HasColumnName("created_at");
            entity.Property(e => e.CreatedBy).HasColumnName("created_by");
            entity.Property(e => e.Name)
                .HasMaxLength(255)
                .HasColumnName("name");
            entity.Property(e => e.RelatedId)
                .HasMaxLength(36)
                .HasColumnName("related_id");
        });

        modelBuilder.Entity<Messages>(entity =>
        {
            entity.HasKey(e => e.MsgId).HasName("PRIMARY");

            entity.ToTable("messages");

            entity.HasIndex(e => new { e.ChannelId, e.CreatedAt }, "idx_channel_time");

            entity.HasIndex(e => e.SenderUid, "idx_sender_uid");

            entity.Property(e => e.MsgId)
                .HasColumnType("bigint(20) unsigned")
                .HasColumnName("msg_id");
            entity.Property(e => e.Body)
                .HasColumnType("text")
                .HasColumnName("body");
            entity.Property(e => e.ChannelId).HasColumnName("channel_id");
            entity.Property(e => e.CreatedAt)
                .HasDefaultValueSql("current_timestamp()")
                .HasColumnType("datetime")
                .HasColumnName("created_at");
            entity.Property(e => e.SenderUid).HasColumnName("sender_uid");

            entity.HasOne(d => d.Channel).WithMany(p => p.Messages)
                .HasForeignKey(d => d.ChannelId)
                .HasConstraintName("fk_messages_channel");
        });

        modelBuilder.Entity<Notifications>(entity =>
        {
            entity.HasKey(e => e.NotifId).HasName("PRIMARY");

            entity.ToTable("notifications");

            entity.HasIndex(e => e.ChannelId, "idx_notif_channel");

            entity.HasIndex(e => new { e.RecipientUid, e.Seen }, "idx_recipient_seen");

            entity.Property(e => e.NotifId)
                .HasColumnType("bigint(20) unsigned")
                .HasColumnName("notif_id");
            entity.Property(e => e.Body)
                .HasColumnType("text")
                .HasColumnName("body");
            entity.Property(e => e.ChannelId).HasColumnName("channel_id");
            entity.Property(e => e.CreatedAt)
                .HasDefaultValueSql("current_timestamp()")
                .HasColumnType("datetime")
                .HasColumnName("created_at");
            entity.Property(e => e.RecipientUid).HasColumnName("recipient_uid");
            entity.Property(e => e.Seen).HasColumnName("seen");
            entity.Property(e => e.Title)
                .HasMaxLength(255)
                .HasColumnName("title");

            entity.HasOne(d => d.Channel).WithMany(p => p.Notifications)
                .HasForeignKey(d => d.ChannelId)
                .OnDelete(DeleteBehavior.SetNull)
                .HasConstraintName("fk_notif_channel");
        });

        OnModelCreatingPartial(modelBuilder);
    }

    partial void OnModelCreatingPartial(ModelBuilder modelBuilder);
}
