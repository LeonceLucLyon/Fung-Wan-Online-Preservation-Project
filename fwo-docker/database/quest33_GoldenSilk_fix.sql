-- ============================================================
-- Quest 33 (DongYing / NPC 487) "Golden Silk" legendary fix
-- ------------------------------------------------------------
-- Root cause: quest handler 83033 gates the armor on
--   fCheckUniqueItem(itemid, 487) == 1
-- which requires uniqueitem.CharID == 487 (matching OriginatorID,
-- exactly like the working MudBuddha set sits on CharID = 80495).
-- The 20 Golden Silk pieces were parked on CharID = 80003 (the
-- BSTie script-NPC), so the check failed for all of them ->
-- "the armor is long gone" on talk, nothing on kill.
--
-- This restores them to the NPC so CheckForArmor() succeeds.
-- NPCFlag (=1), OriginatorID (=487) and revertToID (=487) are
-- already correct and are left untouched.
-- ============================================================

UPDATE uniqueitem
SET CharID = 487
WHERE ItemID BETWEEN 6815749 AND 6815768;
