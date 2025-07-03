-- Organization System Database Schema - Triggers Only
-- These triggers should be executed after the tables are created

-- Organization member count triggers
CREATE TRIGGER IF NOT EXISTS `update_org_member_count_insert` 
AFTER INSERT ON `organization_members`
FOR EACH ROW 
BEGIN
  IF NEW.status = 'active' THEN
    UPDATE `organizations` SET 
      `updated_at` = CURRENT_TIMESTAMP 
    WHERE `org_id` = NEW.org_id;
  END IF;
END;

CREATE TRIGGER IF NOT EXISTS `update_org_member_count_update` 
AFTER UPDATE ON `organization_members`
FOR EACH ROW 
BEGIN
  IF OLD.status != NEW.status THEN
    UPDATE `organizations` SET 
      `updated_at` = CURRENT_TIMESTAMP 
    WHERE `org_id` = NEW.org_id;
  END IF;
END;

CREATE TRIGGER IF NOT EXISTS `update_org_member_count_delete` 
AFTER DELETE ON `organization_members`
FOR EACH ROW 
BEGIN
  UPDATE `organizations` SET 
    `updated_at` = CURRENT_TIMESTAMP 
  WHERE `org_id` = OLD.org_id;
END;

-- Financial balance update triggers
CREATE TRIGGER IF NOT EXISTS `update_org_balance_insert` 
AFTER INSERT ON `organization_transactions`
FOR EACH ROW 
BEGIN
  IF NEW.status = 'completed' AND NEW.finance_id IS NOT NULL THEN
    IF NEW.transaction_type IN ('income', 'refund') THEN
      UPDATE `organization_finances` SET 
        `balance` = `balance` + NEW.amount,
        `updated_at` = CURRENT_TIMESTAMP
      WHERE `finance_id` = NEW.finance_id;
    ELSEIF NEW.transaction_type IN ('expense', 'fee') THEN
      UPDATE `organization_finances` SET 
        `balance` = `balance` - NEW.amount,
        `updated_at` = CURRENT_TIMESTAMP
      WHERE `finance_id` = NEW.finance_id;
    END IF;
  END IF;
END;

CREATE TRIGGER IF NOT EXISTS `update_org_balance_update` 
AFTER UPDATE ON `organization_transactions`
FOR EACH ROW 
BEGIN
  IF OLD.status != NEW.status AND NEW.finance_id IS NOT NULL THEN
    -- Reverse old transaction effect if it was completed
    IF OLD.status = 'completed' THEN
      IF OLD.transaction_type IN ('income', 'refund') THEN
        UPDATE `organization_finances` SET 
          `balance` = `balance` - OLD.amount
        WHERE `finance_id` = OLD.finance_id;
      ELSEIF OLD.transaction_type IN ('expense', 'fee') THEN
        UPDATE `organization_finances` SET 
          `balance` = `balance` + OLD.amount
        WHERE `finance_id` = OLD.finance_id;
      END IF;
    END IF;
    
    -- Apply new transaction effect if now completed
    IF NEW.status = 'completed' THEN
      IF NEW.transaction_type IN ('income', 'refund') THEN
        UPDATE `organization_finances` SET 
          `balance` = `balance` + NEW.amount,
          `updated_at` = CURRENT_TIMESTAMP
        WHERE `finance_id` = NEW.finance_id;
      ELSEIF NEW.transaction_type IN ('expense', 'fee') THEN
        UPDATE `organization_finances` SET 
          `balance` = `balance` - NEW.amount,
          `updated_at` = CURRENT_TIMESTAMP
        WHERE `finance_id` = NEW.finance_id;
      END IF;
    END IF;
  END IF;
END; 