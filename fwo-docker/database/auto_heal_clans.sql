-- Auto-heal clan NPC leaders on every server startup.
-- SMART version: split-field logic.
--
--   clan.Type            ALWAYS set to NPC AttribID  (recruiter always works)
--   npcattribdyn.IsDead  0 when clan empty, 1 when clan has PCs
--                        (NPC physically spawns only when no PC leader)
--
-- Result:
--   - Empty clan      -> NPC respawns, recruiter works
--   - Clan with PC(s) -> NPC stays dead, recruiter works, no duplicate leader
--   - Healthy clan    -> No-op (writes match existing values)
--
-- ClanID 1 -> SSC (NPC AttribID 384)
-- ClanID 2 -> KOH (NPC AttribID 395)
-- ClanID 3 -> MXC (NPC AttribID 382)
-- ClanID 4 -> SWC (NPC AttribID 387)
-- ClanID 5 -> SMC (NPC AttribID 400)

USE fwworlddevdb;

-- Always restore clan.Type so recruiter works regardless of clan state
UPDATE clan SET Type=384 WHERE ClanID=1;
UPDATE clan SET Type=395 WHERE ClanID=2;
UPDATE clan SET Type=382 WHERE ClanID=3;
UPDATE clan SET Type=387 WHERE ClanID=4;
UPDATE clan SET Type=400 WHERE ClanID=5;

-- Build set of ClanIDs (1-5) that currently have at least one PC member
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

-- Resurrect NPC if clan empty (no PCs)
UPDATE npcattribdyn SET IsDead=0 WHERE AttribID=384 AND 1 NOT IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE npcattribdyn SET IsDead=0 WHERE AttribID=395 AND 2 NOT IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE npcattribdyn SET IsDead=0 WHERE AttribID=382 AND 3 NOT IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE npcattribdyn SET IsDead=0 WHERE AttribID=387 AND 4 NOT IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE npcattribdyn SET IsDead=0 WHERE AttribID=400 AND 5 NOT IN (SELECT ClanID FROM tmp_pc_clans);

-- Suppress NPC spawn if clan has PCs (prevents duplicate-leader bug)
UPDATE npcattribdyn SET IsDead=1 WHERE AttribID=384 AND 1 IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE npcattribdyn SET IsDead=1 WHERE AttribID=395 AND 2 IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE npcattribdyn SET IsDead=1 WHERE AttribID=382 AND 3 IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE npcattribdyn SET IsDead=1 WHERE AttribID=387 AND 4 IN (SELECT ClanID FROM tmp_pc_clans);
UPDATE npcattribdyn SET IsDead=1 WHERE AttribID=400 AND 5 IN (SELECT ClanID FROM tmp_pc_clans);

DROP TABLE tmp_pc_clans;
