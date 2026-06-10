-- ============================================================
-- Auto-heal clan NPC leaders on every server startup  (v2 - clan refactor)
-- ------------------------------------------------------------
-- CHANGE FROM v1: player-occupied clans now get clan.Type = 0 (player-led),
-- NOT the NPC AttribID. v1 forced Type = NPC AttribID for every clan, which
-- kept the village recruiter working but broke the Clan Advisor (it bails
-- unless Type == 0) and blocked proper RANK_LEADER promotion on takeover.
--
-- The recruiter is now fixed (script 75024) to enroll MEMBERS at Type == 0,
-- so we no longer need to force Type. The split-field logic is preserved,
-- just with the correct Type value for the player-led case:
--
--   Empty clan       -> Type = NPC AttribID, IsDead = 0   (NPC respawns & leads)
--   Player-occupied  -> Type = 0,            IsDead = 1   (player leads, NPC stays dead)
--
-- ClanID 1->SSC(384)  2->KOH(395)  3->MXC(382)  4->SWC(387)  5->SMC(400)
-- ============================================================
USE fwworlddevdb;

-- ClanIDs (1-5) that currently have at least one PC member
DROP TABLE IF EXISTS tmp_pc_clans;
CREATE TABLE tmp_pc_clans (ClanID INT NOT NULL, PRIMARY KEY (ClanID)) ENGINE=MyISAM;
INSERT IGNORE INTO tmp_pc_clans (ClanID)
  SELECT DISTINCT ClanID FROM intdata_0 WHERE ClanID BETWEEN 1 AND 5 AND CharID>0
  UNION SELECT DISTINCT ClanID FROM intdata_1 WHERE ClanID BETWEEN 1 AND 5 AND CharID>0
  UNION SELECT DISTINCT ClanID FROM intdata_2 WHERE ClanID BETWEEN 1 AND 5 AND CharID>0
  UNION SELECT DISTINCT ClanID FROM intdata_3 WHERE ClanID BETWEEN 1 AND 5 AND CharID>0
  UNION SELECT DISTINCT ClanID FROM intdata_4 WHERE ClanID BETWEEN 1 AND 5 AND CharID>0
  UNION SELECT DISTINCT ClanID FROM intdata_5 WHERE ClanID BETWEEN 1 AND 5 AND CharID>0
  UNION SELECT DISTINCT ClanID FROM intdata_6 WHERE ClanID BETWEEN 1 AND 5 AND CharID>0
  UNION SELECT DISTINCT ClanID FROM intdata_7 WHERE ClanID BETWEEN 1 AND 5 AND CharID>0
  UNION SELECT DISTINCT ClanID FROM intdata_8 WHERE ClanID BETWEEN 1 AND 5 AND CharID>0
  UNION SELECT DISTINCT ClanID FROM intdata_9 WHERE ClanID BETWEEN 1 AND 5 AND CharID>0;

-- ---- Player-occupied clans: player-led (Type=0), NPC stays dead ----
UPDATE clan SET Type=0 WHERE ClanID=1 AND 1 IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE clan SET Type=0 WHERE ClanID=2 AND 2 IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE clan SET Type=0 WHERE ClanID=3 AND 3 IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE clan SET Type=0 WHERE ClanID=4 AND 4 IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE clan SET Type=0 WHERE ClanID=5 AND 5 IN (SELECT ClanID FROM tmp_pc_clans);

UPDATE npcattribdyn SET IsDead=1 WHERE AttribID=384 AND 1 IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE npcattribdyn SET IsDead=1 WHERE AttribID=395 AND 2 IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE npcattribdyn SET IsDead=1 WHERE AttribID=382 AND 3 IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE npcattribdyn SET IsDead=1 WHERE AttribID=387 AND 4 IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE npcattribdyn SET IsDead=1 WHERE AttribID=400 AND 5 IN (SELECT ClanID FROM tmp_pc_clans);

-- ---- Empty clans: NPC-led (Type=NPC AttribID), NPC respawns ----
UPDATE clan SET Type=384 WHERE ClanID=1 AND 1 NOT IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE clan SET Type=395 WHERE ClanID=2 AND 2 NOT IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE clan SET Type=382 WHERE ClanID=3 AND 3 NOT IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE clan SET Type=387 WHERE ClanID=4 AND 4 NOT IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE clan SET Type=400 WHERE ClanID=5 AND 5 NOT IN (SELECT ClanID FROM tmp_pc_clans);

UPDATE npcattribdyn SET IsDead=0 WHERE AttribID=384 AND 1 NOT IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE npcattribdyn SET IsDead=0 WHERE AttribID=395 AND 2 NOT IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE npcattribdyn SET IsDead=0 WHERE AttribID=382 AND 3 NOT IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE npcattribdyn SET IsDead=0 WHERE AttribID=387 AND 4 NOT IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE npcattribdyn SET IsDead=0 WHERE AttribID=400 AND 5 NOT IN (SELECT ClanID FROM tmp_pc_clans);

DROP TABLE tmp_pc_clans;
