local function fDropAllUniqueItems(oCharID, oKillerID, bRelicOnly)
  local i, j, oItem, nQuantity
  
  local tComponents = {}
  local tDrops = {}
  local nDropCount = 0
  local nGroup, szString, bHasComponents, oLootID, nWeaponGroup, nWeaponType, nWeaponSlot, nDurability, nHardness, bRemove, nTarLevel, tItemData
  local tUniqueItemList = {}
  local tUniqueItemSlot = {}
  local nUniqueDrop = 0
  local tSlots, bRefreshFlags
  bHasComponents = nil
  tUniqueItemList.n = 0
  tUniqueItemSlot.n = 0
  nWeaponGroup, nWeaponType, nWeaponSlot = GetActiveWeapon(oCharID)
  nWeaponSlot = nWeaponSlot or 0
  if oKillerID ~= 0 and GetEntityType(oKillerID) == 0 then
    nTarLevel = GetLevel(oKillerID)
  else
    nTarLevel = nil
  end
  if not bRelicOnly and not fCheckWarRules(oCharID, WARRULE_ITEMDROP) then
    bRelicOnly = 1
  end
  tSlots = fGetCharSlots(0, 1)
  for i = 1, tSlots.n do
    if 16 <= nDropCount then
      break
    end
    oItem, nQuantity = GetItemInSlot(oCharID, tSlots[i])
    if oItem and nQuantity and oItem ~= 0 and 0 < nQuantity then
      nGroup = GetHiValue(oItem)
      if nGroup == 21 then
        if DestroyItem(oCharID, oItem) then
          SendCharLog(1408, oItem, oCharID)
          nDropCount = nDropCount + 1
          bRefreshFlags = 1
          szString = format("ItemID_%d", nDropCount)
          tDrops[szString] = oItem
          for j = 1, 5 do
            szString = format("ComponentID%d_%d", j, nDropCount)
            tDrops[szString] = 0
          end
        end
      elseif oItem == ITEM_PROXYRELIC then
        fOnWarEvent(oCharID, WAREVENT_CHARDEATH, tSlots[i])
        bRefreshFlags = 1
      elseif not bRelicOnly and 100 < nGroup then
        tUniqueItemList.n = tUniqueItemList.n + 1
        tUniqueItemSlot.n = tUniqueItemSlot.n + 1
        tUniqueItemList[tUniqueItemList.n] = oItem
        tUniqueItemSlot[tUniqueItemSlot.n] = tSlots[i]
        if nUniqueDrop == 0 then
          bDrop = nil
          if nTarLevel and 0 < nTarLevel then
            tItemData = GetItemData(oItem)
            if tItemData and nTarLevel >= tItemData.LevelGroup and RollDice(1, 100, 0) <= 18 then
              bDrop = 1
            end
          end
          if bDrop then
            local tUniqueItemData = GetUniqueItemData(oItem)
            bHasComponents = 1
            tComponents = GetComponent(oCharID, tSlots[i])
            if not tComponents then
              tComponents = {}
              tComponents[1] = 0
              tComponents[2] = 0
              tComponents[3] = 0
              tComponents[4] = 0
              tComponents[5] = 0
            end
            nDurability, nHardness = GetDurability(oCharID, tSlots[i])
            nDurability = nDurability or 0
            nHardness = nHardness or 0
            if tSlots[i] == nWeaponSlot then
              ExecuteScript(20522, oCharID, 0)
            elseif tSlots[i] >= tEQUIP[1] and tSlots[i] <= tEQUIP[tEQUIP.n] and i ~= EQUIP_WEAPON then
              fRemoveItemFromCharacter(oCharID, oItem, tSlots[i])
            end
            if i == EQUIP_WEAPON then
              SetReadyWeapon(oCharID, 0)
            end
            if RemoveItem(oCharID, oItem, 1, tSlots[i]) then
              SendCharLog(205, oItem, oCharID)
              bRefreshFlags = 1
              nDropCount = nDropCount + 1
              nUniqueDrop = nUniqueDrop + 1
              szString = format("ItemID_%d", nDropCount)
              tDrops[szString] = oItem
              szString = format("Durability_%d", nDropCount)
              tDrops[szString] = nDurability
              szString = format("Hardness_%d", nDropCount)
              tDrops[szString] = nHardness
              for j = 1, 5 do
                szString = format("ComponentID%d_%d", j, nDropCount)
                tDrops[szString] = tComponents[j]
              end
              if tUniqueItemData and 0 < tUniqueItemData.DecayCounter then
                SetDecayCounter(oItem, tUniqueItemData.DecayCounter)
              end
            end
          end
        end
      end
    end
  end
  if 0 < nDropCount then
    local tLootArray = {}
    for i = 1, nDropCount do
      szString = format("ItemID_%d", i)
      tLootArray[i] = tDrops[szString]
      ALog(1, "1040 : CHAR=%u : ITEM=%u :: PC drops loot", oCharID, tLootArray[i])
    end
    oLootID = DropLoot(oKillerID, oCharID, tLootArray)
    if oLootID and bHasComponents then
      tDrops.n = nDropCount
      if not DropLootExt1(oLootID, tDrops) then
        return -1
      end
    elseif not oLootID then
    end
  end
  if not bRelicOnly and nUniqueDrop == 0 and 0 < tUniqueItemList.n then
    local nDropIdx = RollDice(1, tUniqueItemList.n, 0)
    local tItemData = GetItemData(tUniqueItemList[nDropIdx])
    local tUniqueItemData = GetUniqueItemData(tUniqueItemList[nDropIdx])
    if tItemData and tUniqueItemData and 0 < tItemData.DecayValue then
      local nDecayDrop = RoundUp(tItemData.DecayValue * 0.2)
      if nDecayDrop < tUniqueItemData.DecayCounter then
        SetDecayCounter(tUniqueItemList[nDropIdx], tUniqueItemData.DecayCounter - nDecayDrop)
      else
        if tUniqueItemSlot[nDropIdx] == nWeaponSlot then
          ExecuteScript(20522, oCharID, 0)
        elseif tUniqueItemSlot[nDropIdx] >= tEQUIP[1] and tUniqueItemSlot[nDropIdx] <= tEQUIP[tEQUIP.n] and tUniqueItemSlot[nDropIdx] ~= tEQUIP[EQUIP_WEAPON] then
          fRemoveItemFromCharacter(oCharID, tUniqueItemList[nDropIdx], tUniqueItemSlot[nDropIdx])
        end
        if tUniqueItemSlot[nDropIdx] == tEQUIP[EQUIP_WEAPON] then
          SetReadyWeapon(oCharID, 0)
        end
        if RemoveItem(oCharID, tUniqueItemList[nDropIdx], 1, tUniqueItemSlot[nDropIdx]) then
          SendCharLog(207, tUniqueItemList[nDropIdx], oCharID)
          SendGenMessage(oCharID, 2132, 1)
          bRefreshFlags = 1
        end
      end
    end
  end
  if bRefreshFlags then
    ExecuteScript(28733, oCharID, 0)
  end
  return nDropCount
end

local function fDropRandomItem(oCharID, oKillerID)
  local i, oItemID, nQuantity, nGroup
  local tDropList = {}
  local nDropIdx, tItemData, tSlots
  tDropList.n = 0
  tSlots = fGetCharSlots(0, 1)
  for i = 1, tSlots.n do
    oItemID, nQuantity = GetItemInSlot(oCharID, tSlots[i])
    if oItemID and nQuantity and 0 < oItemID and 0 < nQuantity then
      nGroup = GetHiValue(oItemID)
      if nGroup < 100 and nGroup ~= 21 and oItemID ~= ITEM_PROXYRELIC then
        tItemData = GetItemData(oItemID)
        if tItemData and tItemData.NoTransFlag ~= 1 then
          tDropList[tDropList.n + 1] = tSlots[i]
          tDropList.n = tDropList.n + 1
        end
      end
    end
  end
  if tDropList.n == 0 then
    return 0
  end
  nDropIdx = RollDice(1, tDropList.n, 0)
  nDropIdx = tDropList[nDropIdx]
  oItemID, nQuantity = GetItemInSlot(oCharID, nDropIdx)
  if oItemID and nQuantity and 0 < oItemID and 0 < nQuantity then
    if nDropIdx == tEQUIP[EQUIP_WEAPON] then
      ExecuteScript(20522, oCharID, 0)
      SetReadyWeapon(oCharID, 0)
    elseif nDropIdx >= tEQUIP[1] and nDropIdx <= tEQUIP[tEQUIP.n] then
      fRemoveItemFromCharacter(oCharID, oItemID, nDropIdx)
    end
    if oKillerID and 0 < oKillerID then
      local tDropTable = {}
      local nDurability, nHardness
      local tComponents = GetComponent(oCharID, nDropIdx)
      local j, szString
      local tLootArray = {}
      local oLootID
      if not tComponents then
        tComponents = {}
        tComponents[1] = 0
        tComponents[2] = 0
        tComponents[3] = 0
        tComponents[4] = 0
        tComponents[5] = 0
      end
      nDurability, nHardness = GetDurability(oCharID, nDropIdx)
      nDurability = nDurability or 0
      nHardness = nHardness or 0
      if not RemoveItem(oCharID, oItemID, nQuantity, nDropIdx) then
        return 0
      end
      szString = format("ItemID_%d", 1)
      tDropTable[szString] = oItemID
      szString = format("Durability_%d", 1)
      tDropTable[szString] = nDurability
      szString = format("Hardness_%d", 1)
      tDropTable[szString] = nHardness
      for j = 1, 5 do
        szString = format("ComponentID%d_%d", j, 1)
        tDropTable[szString] = tComponents[j]
      end
      tDropTable.n = 1
      tLootArray[1] = oItemID
      ALog(1, "1040 : CHAR=%u : ITEM=%u :: PC drops loot", oCharID, oItemID)
      oLootID = DropLoot(oKillerID, oCharID, tLootArray)
      if oLootID and oLootID ~= 0 then
        if not DropLootExt1(oLootID, tDropTable) then
          return 0
        end
      else
        return 0
      end
    elseif not RemoveItem(oCharID, oItemID, nQuantity, nDropIdx) then
      return 0
    end
  else
    return 0
  end
  return oItemID
end

local function fGetActiveParty(oCharID)
  local nPartyID, tCharList, tEntityList, i, nEntityParty
  tCharList = {}
  tCharList[1] = oCharID
  tCharList.n = 1
  nPartyID = GetPartyID(oCharID)
  if nPartyID and nPartyID ~= 0 then
    tEntityList = GetEntitiesInArea(oCharID, 5000, 1)
    if tEntityList and tEntityList.n > 0 then
      for i = 1, tEntityList.n do
        if tEntityList[i] ~= oCharID then
          nEntityParty = GetPartyID(tEntityList[i])
          if nEntityParty and nEntityParty == nPartyID then
            tCharList.n = tCharList.n + 1
            tCharList[tCharList.n] = tEntityList[i]
          end
        end
      end
    end
  end
  if tCharList.n < 2 then
    return nil
  end
  return tCharList
end

local function fClearLootSlots(oCharID)
  local Item, Quantity, Group
  for i = 1, tLOOTSLOT.n do
    Item, Quantity = GetItemInSlot(oCharID, tLOOTSLOT[i])
    if Item and Quantity and Item ~= 0 and 0 < Quantity then
      Group = GetHiValue(Item)
      if Group == 21 or 100 < Group then
        RemoveItem(oCharID, Group, GetLowValue(Item), Quantity, tLOOTSLOT[i])
      end
    end
  end
end

local oCharID, oTargetID, nParam, bSelfKill, nCharType, nTarType, nTarHP, nState, oDuelChar, nCharClan, nCharGuild, nCharRank, nTarClan, nTarGuild, nTarRank, nRating, nResult, bIsGM, oGuardID, nWarSceneType
oCharID = GetEventEntity1()
oTargetID, nParam = GetScriptParams()
oTargetID = oTargetID or GetPlayerScratchData(oCharID, 30)
if not oTargetID or oTargetID and oTargetID == 0 then
  return
end
nParam = nParam or 0
if oCharID == oTargetID then
  bSelfKill = 1
else
  bSelfKill = nil
end
nCharType = GetEntityType(oCharID)
nTarType = GetEntityType(oTargetID)
nTarHP = GetCurrentHitPoints(oTargetID)
nState = GetEntityState(oTargetID)
if not (nCharType and nTarType and nTarHP) or not nState then
  return
end
if nState == 1 then
  return
end
if 0 < nTarHP then
  return
end
SetPlayerScratchData(oTargetID, 44, oCharID)
fCheckCancelGuard(oTargetID)
if nTarType == 0 and not fCanCombat(oTargetID) then
  SetCurrentHitPoints(oTargetID, 1)
  return
end
if nCharType == 0 then
  bIsGM = fIsGM(oCharID)
else
  bIsGM = nil
end
if nTarType == 1 then
  if fCheckNextStage(oTargetID, oCharID) then
    return
  end
  if nCharType == 0 then
    SetNPCDropLootFlag(oTargetID, fCheckPKPointResult(oCharID, PK_RESULT_LOOTDROP))
  end
end
if nCharType == 0 and nTarType == 0 then
  oDuelChar = GetPlayerScratchData(oTargetID, 43)
  if oDuelChar and oDuelChar == oCharID then
    ExecuteScript(20519, 2, oTargetID)
    SetCurrentHitPoints(oTargetID, 1)
    return
  end
end
if not SetEntityState(oTargetID, 1) then
  return
end
SetPlayerScratchData(oTargetID, 9, GetTickcount())
SetPlayerScratchData(oTargetID, 11, oCharID)
if nTarType == 0 then
  ExecuteScript(28732, oTargetID, 1194)
  ExecuteScript(32862, oTargetID, 1194)
  SetZoneFlag(oTargetID, 0)
  if bSelfKill then
    SetLastKillerID(oTargetID, 0)
  else
    SetLastKillerID(oTargetID, oCharID)
  end
  fClearLootSlots(oTargetID)
  if nCharType == 0 and not bSelfKill and nParam ~= 3 then
    if not bIsGM and fCheckPKPenalty(oCharID, oTargetID) then
      fSetCharPKPoints(oCharID, oTargetID, PK_TYPE_KILL)
    elseif not bIsGM then
      fSetCharHeroPoints(oCharID, oTargetID, HERO_TYPE_KILL)
    end
    oDuelChar = GetPlayerScratchData(oTargetID, 43)
    if oDuelChar and oDuelChar ~= 0 then
      ExecuteScript(20519, 255, oTargetID)
    end
  else
    oDuelChar = GetPlayerScratchData(oTargetID, 43)
    if oDuelChar and oDuelChar ~= 0 then
      ExecuteScript(20519, 255, oTargetID)
    end
  end
end
if nCharType == 0 then
  local nCurTarget = GetPlayerScratchData(oCharID, 1)
  if nCurTarget and nCurTarget == oTargetID then
    ExecuteScript(20489)
  end
  if nTarType == 0 then
    nCharClan, nCharGuild, nCharRank = GetAffiliation(oCharID)
    nTarClan, nTarGuild, nTarRank = GetAffiliation(oTargetID)
    ALog(2, "2001 : CHAR=%u :: Player kills another player character", oTargetID)
    SetPlayerScratchData(oTargetID, 1, 0)
    SetGameStats(oCharID, 0, 1, 0, 0, 0)
    if not bSelfKill and nParam ~= 3 then
      if not bIsGM and nCharClan and nTarClan and 0 < nCharClan and 0 < nTarClan and nCharClan ~= nTarClan then
        fChangeClanRating(oCharID, nTarClan, -1)
      end
      if nTarClan and nTarClan ~= 0 and SUCCEEDED(fIsHigherRank(nTarRank, RANK_MEMBER)) and fIsClanAtWar(nTarClan) then
        nCharClan = nCharClan or 0
        if nTarRank == RANK_LEADER then
          LogWarAction(nCharClan, nTarClan, 7, oCharID, oTargetID, nTarClan)
          if nCharClan ~= 0 and nCharClan ~= nTarClan and fCheckClanWar(nCharClan, nTarClan) then
            fAddClanScore(nCharClan, nTarClan, 100, -100, WARSCORE_ACTION_LEADERKILL, oCharID)
          end
        elseif nTarRank == RANK_MINISTER then
          LogWarAction(nCharClan, nTarClan, 8, oCharID, oTargetID, nTarClan)
          if nCharClan ~= 0 and nCharClan ~= nTarClan and fCheckClanWar(nCharClan, nTarClan) then
            fAddClanScore(nCharClan, nTarClan, 50, -50, WARSCORE_ACTION_MINISTERKILL, oCharID)
          end
        elseif nTarRank == RANK_MASTER then
          LogWarAction(nCharClan, nTarClan, 9, oCharID, oTargetID, nTarClan)
        end
      end
      if not bIsGM then
        SendGenMessage(oTargetID, 1133, 1, oCharID)
      end
      SendGenMessage(oCharID, 1135, 1, oTargetID)
      SetPlayerScratchData(oCharID, 31, oTargetID)
      local nTargetLevel = GetLevel(oTargetID)
      if nTargetLevel and 0 < nTargetLevel then
        fUpdateArenaScore(oCharID, nTargetLevel)
      end
    else
      SendGenMessage(oTargetID, 1115)
      SetPlayerScratchData(oCharID, 31, 0)
    end
    nWarSceneType = fIsInWarScene(oCharID)
    if not nWarSceneType and not fIsClosedPK() then
      local nDrops = 0
      if bSelfKill and oDuelChar and oDuelChar == 0 then
        nDrops = fDropAllUniqueItems(oTargetID, 0)
      elseif oDuelChar and oDuelChar == 0 then
        nDrops = fDropAllUniqueItems(oTargetID, oCharID)
      end
      if 0 < nDrops then
        nResult = GetSceneZoneFlag(oTargetID)
        if nResult and nResult == 1 and nTarRank ~= RANK_LEADER then
          SetZoneKillFlag(oTargetID, 1)
        end
        ExecuteScript(28733, oTargetID, 0)
      elseif fCheckPKPointResult(oTargetID, PK_RESULT_ITEMDROP) == 1 and 0 < fDropRandomItem(oTargetID, oCharID) then
        SendGenMessage(oTargetID, 2132, 1)
      end
    elseif nWarSceneType then
      if 0 >= fDropAllUniqueItems(oTargetID, 0, 1) and fCheckPKPointResult(oTargetID, PK_RESULT_ITEMDROP) == 1 and 0 < fDropRandomItem(oTargetID, oCharID) then
        SendGenMessage(oTargetID, 2132, 1)
      end
      if nWarSceneType == SCENETYPE_ARENA then
      elseif nWarSceneType == SCENETYPE_IGR then
        local nScore, tIGRData
        local nSceneID = GetSceneID(oCharID)
        local nTeamID = GetTeamID(oCharID)
        local nTarTeamID = GetTeamID(oTargetID)
        if nSceneID and nTeamID and nTarTeamID and nTeamID ~= 0 and nTeamID ~= nTarTeamID then
          tIGRData = GetIGRData(nSceneID)
          if tIGRData and tIGRData.Status == IGR_STATUS_STARTED then
            SetIGRFlags(nSceneID, oCharID, IGR_FLAGS_KILLED)
            if tIGRData.Type == IGR_TYPE_TEAMSURVIVALMATCH then
              fUpdateTeamOfStatus(nSceneID, nTarTeamID)
            end
            nScore = GetIGRScore(nSceneID, oCharID)
            if nScore then
              SetIGRScore(nSceneID, oCharID, nScore + 1)
            end
            ExecuteScript(32858, nSceneID, 1)
          end
        end
      elseif nWarSceneType == SCENETYPE_COMPETITIVE then
        local nScore, tArenaData
        local nSceneID = GetSceneID(oCharID)
        local nTeamID = GetTeamID(oCharID)
        local nTarTeamID = GetTeamID(oTargetID)
        if nSceneID and nTeamID and nTarTeamID and nTeamID ~= 0 and nTeamID ~= nTarTeamID then
          tArenaData = GetIGRData(nSceneID)
          if tArenaData and tArenaData.Status == ARENA_STATUS_STARTED then
            SetIGRFlags(nSceneID, oCharID, ARENA_FLAGS_KILLED)
            if tArenaData.Type == ARENA_TYPE_TEAMSURVIVALMATCH then
              fUpdateTeamOfStatus(nSceneID, nTarTeamID)
            end
            nScore = GetIGRScore(nSceneID, oCharID)
            if nScore then
              SetIGRScore(nSceneID, oCharID, nScore + 1)
            end
            ExecuteScript(32860, nSceneID, 1)
          end
        end
      else
        fOnWarEvent(oTargetID, WAREVENT_CHARDEATH)
      end
    end
  elseif nTarType == 1 then
    local nNameID, nClanType, nAttribID
    SetGameStats(oCharID, 1, 0, 0, 0, 0)
    nNameID = GetNameID(oTargetID)
    if nNameID and nNameID ~= 0 then
      SendGenMessage(oCharID, 1134, 1, nNameID)
    end
    ReactToQuest(oCharID, oTargetID)
    nCharClan, nCharGuild, nCharRank = GetAffiliation(oCharID)
    nTarClan = GetClanID(oTargetID)
    if not bIsGM and nCharClan and nTarClan and 0 < nTarClan and nTarClan <= MAX_CLAN then
      nClanType = GetClanType(nTarClan)
      nAttribID = GetNPCAttrib(oTargetID)
      if nClanType and nAttribID and nClanType == nAttribID and SetClanType(nTarClan, 0) then
        ALog(3, "3005 : CHAR=%u : CLAN=%u : TYPE=%u :: Player kills NPC Clan Leader", oTargetID, nTarClan, nAttribID)
        if nCharClan == 0 or nCharClan == nTarClan then
          if nCharClan == nTarClan then
            if SUCCEEDED(fSetRank(oCharID, RANK_LEADER)) then
              ClearAggro(nTarClan, oCharID)
              SetWaitPeriod(oCharID, 0)
              fSetPKWarning(oCharID, PKWARNING_CLEAR)
            end
          elseif SUCCEEDED(fAddToGuild(oCharID, 0, RANK_LEADER, nTarClan)) then
            ClearAggro(nTarClan, oCharID)
            SetWaitPeriod(oCharID, 0)
            fUpdateNewClanRating(oCharID, nTarClan)
            fSetPKWarning(oCharID, PKWARNING_CLEAR)
          end
        else
          fChooseNewLeader(nTarClan)
        end
      end
      local nRatingPlus, nRatingMinus, nEnemyClanID, bGivePlus
      nRatingPlus, nRatingMinus, nEnemyClanID = GetNPCClanInfo(oTargetID)
      if nRatingPlus and nRatingMinus and nEnemyClanID then
        if nRatingMinus ~= 0 and SUCCEEDED(fChangeClanRating(oCharID, nTarClan, -nRatingMinus)) then
          SendGenMessage(oCharID, 2116, 1, CLAN_STRING_OFFSET + nTarClan)
        end
        if nRatingPlus ~= 0 then
          if nEnemyClanID <= MAX_CLAN then
            nClanType = GetClanType(nEnemyClanID)
            if not nClanType or nClanType ~= 0 then
              bGivePlus = 1
            end
          else
            bGivePlus = 1
          end
          if bGivePlus and SUCCEEDED(fChangeClanRating(oCharID, nEnemyClanID, nRatingPlus)) then
            SendGenMessage(oCharID, 2115, 1, CLAN_STRING_OFFSET + nEnemyClanID)
          end
        end
      end
    end
    ExecuteScript(20526, oTargetID, nParam)
    -- ===================== HERO POINTS ON MOB KILL (custom mod) =====================
    -- Awards hero points to the killer (oCharID) for killing a monster/NPC (oTargetID).
    -- Amount = mob level * multiplier   (lvl 1 mob -> 1pt, lvl 200 mob -> 200pt at 1x).
    -- The multiplier is read live from gameevent ID 999 ("Hero Mob-Kill Multiplier"),
    -- so you can change it any time with SetHeroMult.bat -- no recompile needed.
    -- (0 = OFF, 1 = 1x, 40 = 40x, ... up to 100.)
    local nHeroMobMult = GetGameEventStatus(999)
    if nHeroMobMult and nHeroMobMult > 0 then
      local nMobLevel = GetLevel(oTargetID)
      if nMobLevel and nMobLevel > 0 then
        SetHeroPoints(oCharID, nMobLevel * nHeroMobMult)
      end
    end
    -- =================== END HERO POINTS ON MOB KILL ===================
    -- ===================== RARE LOOT POOLS + GOLD MODIFIER (custom mod) =====================
    -- Slot 1 = the mob's normal treasure-table drop (engine-handled, NOT touched here).
    -- Slots 2-6 each roll independently; any that hit drop together as ONE extra loot bag,
    -- dropped AT THE KILLER (the same DropLoot(killer,killer,..) pattern the game's own mob
    -- drops use, e.g. ScaryTiger/JapCap). That lands the rare bag at the player, clear of
    -- the engine's normal loot bag on the corpse, so both bags are separately clickable.
    -- Each pool's chance = its gameevent Status (0 = OFF, 1-100 = pct). Pool size = no effect
    -- on odds. Up to 5 rares per kill (one per pool).
    local function fRareRoll(nEventID, tPool, nSize)
      local nChance = GetGameEventStatus(nEventID)
      if nChance and nChance > 0 and RollDice(1, 100, 0) <= nChance then
        return tPool[RollDice(1, nSize, 0)]
      end
    end
    local tRareLoot = {}
    local nRareN = 0
    local nRolled
    nRolled = fRareRoll(993, {262175,262176,262177,262178,262179,262186,262187,262188,262189,262191,131290,131291,131292,131293,131294,131295,131296,131297,131298,131299,131300,131301,131302,131303,131304,131305,131306,131307,131308,131309,131310,131311,131312,131313,131314,131315,131316,131317,131318,131319,131320,131321,131322,131323,131324,131326,131328,131329,131332,131333,131334,196816,196817,196818,196819,196820,196821,196822,196823,196824,196825,196826,196827,196828,196829,196830,196831,196832,196833,196834,196835,196836,196837,196838,196839,196840,196841,196842,196843,196844,196849,196850,196851,196852,196853,196854,196855}, 87); if nRolled then nRareN=nRareN+1; tRareLoot[nRareN]=nRolled end   -- Slot 2: Armor / Bracers / Greaves
    nRolled = fRareRoll(992, {852069,852070,852071,852072,852073,852074,852075,852076,852077,852078,852079,852762,720997,720998,720999,721000,721001,721002,721003,721004,721005,721006,721007,721690,65719,65720,65721,65722,65723,65724,65725,65726,65727,65728,65729,65730,65731,65732,65733,65734,65735,65736,65737,65738,65739,65740}, 46); if nRolled then nRareN=nRareN+1; tRareLoot[nRareN]=nRolled end   -- Slot 3: Staff / Bow / Amulets
    nRolled = fRareRoll(991, {786538,786539,786540,786541,786542,786543,786544,786545,786546,786547,786548,787231,917605,917606,917607,917608,917609,917610,917611,917612,917613,917614,917615,918298,655461,655462,655563,655564,655565,655566,655567,655568,655569,655570,655571,655572,655579,655580,655581,655582,655583,655584,655604,655605,655606,1179660,1179661,1179662,1179663,1179664,1179666,1310761,1310762,1310763,1310764,1310765,1310767}, 57); if nRolled then nRareN=nRareN+1; tRareLoot[nRareN]=nRolled end   -- Slot 4: Saber / Sword / Rings / Shoulderpads / Masks
    nRolled = fRareRoll(990, {983152,983153,983424,983425,983426,983427,983428,983429,983430,983431,983432,983433,983434,983435,983436,983437,983438,983439,983440,983441,983442,983443,983444,983445,983446,983447,983448,983449,983450,983451,983452,983453,983454,983455,983456,983457,983458,983459,983460,983461,983462,983463,983464,983465,983466,983467,983468,983469,983470,983471,983472,983473,983474,983475,983476,983477,983478,983479,983480,983481,983482,983483,983484,983485,983486,983487,983488,983489,983490,983491,983492,983493,983494,983495,983496,983497,983498,983499,983500,983501,983502,983503,983504,983505,983506,983507,983508,983509,983510,983511,983512,983513,983514,983515,983516,983517,983518,983519,983520,983521,983522}, 101); if nRolled then nRareN=nRareN+1; tRareLoot[nRareN]=nRolled end   -- Slot 5: Rare components
    nRolled = fRareRoll(989, {983160,983161,983162,983163,983164,983165,983166,983167,983168,983169,983170,983171,983172,983173,983174,983175,983176,983177,983178,983179,983180,983181,983182,983183,983184,983185,983186,983187,983188,983189,983190,983191,983541,983551,983552,983553,983554,983555,983556,983557,983558,983559,983560,983561,983562,983563,983572,983573,983574,983575,983576,983577,983578,983579,983580,983581,983582,983583}, 58); if nRolled then nRareN=nRareN+1; tRareLoot[nRareN]=nRolled end   -- Slot 6: Heavenly components
    if nRareN > 0 then
      DropLoot(oCharID, oCharID, tRareLoot)
    end
    -- GOLD MODIFIER (gameevent 998): adds (mob level * multiplier) gold to the killer, fresh
    -- each kill so it never compounds. 0 = OFF. Normal gold drop is unchanged.
    do
      local nGoldMult = GetGameEventStatus(998)
      if nGoldMult and nGoldMult > 0 then
        local nGoldLvl = GetLevel(oTargetID)
        if nGoldLvl and nGoldLvl > 0 then
          SetGold(oCharID, GetGold(oCharID) + (nGoldLvl * nGoldMult))
        end
      end
    end
    -- =================== END RARE LOOT POOLS + GOLD MODIFIER ===================
    local nScore = GetLevel(oTargetID)
    if nScore and 0 < RoundDown(nScore / 2) then
      fUpdateArenaScore(oCharID, RoundDown(nScore / 2))
    end
    if 1 == 2 then
      local nXPValue = GetXP(oTargetID)
      local nTLevel = GetLevel(oTargetID)
      local tPartyMembers = fGetActiveParty(oCharID)
      local nXPGain
      if nParam == 1 then
        nXPValue = nXPValue * 0.5
      end
      if tPartyMembers then
        local nTotalLevels = 0
        local nXPMultiplier, nXP
        local tLevels = {}
        local i
        for i = 1, tPartyMembers.n do
          tLevels[i] = GetLevel(tPartyMembers[i])
          nTotalLevels = nTotalLevels + tLevels[i]
        end
        nXPValue = RoundUp(nXPValue * (RoundUp((9 * RoundUp(tPartyMembers.n / 2) + 11) / 4) + 100) / 100)
        for i = 1, tPartyMembers.n do
          nState = GetEntityState(tPartyMembers[i])
          if nState ~= 1 then
            if nTLevel >= tLevels[i] then
              nXPMultiplier = 1
            else
              nXPMultiplier = 1 - 0.14 * (tLevels[i] - nTLevel)
              if nXPMultiplier < 0.3 then
                nXPMultiplier = 0.3
              end
            end
            if 0 < nXPMultiplier then
              nXPGain = nXPValue * nXPMultiplier * tLevels[i] / nTotalLevels * fCheckPKPointResult(tPartyMembers[i], PK_RESULT_XPPENALTY)
              nXP = GetXP(tPartyMembers[i])
              nXP = nXP + RoundDown(nXPGain)
              SetXP(tPartyMembers[i], nXP)
              ALog(2, "2012 : CHAR_SRC=%u : CHAR_TGT=%u : XP=%u :: Party kills NPC and gains XP", tPartyMembers[i], oTargetID, RoundDown(nXPGain))
            end
          end
        end
      else
        local nLevel = GetLevel(oCharID)
        local nXPMultiplier, nXPGain
        if nTLevel >= nLevel then
          nXPMultiplier = 1
        else
          nXPMultiplier = 1 - 0.14 * (nLevel - nTLevel)
          if nXPMultiplier < 0.3 then
            nXPMultiplier = 0.3
          end
        end
        nXPGain = nXPValue * nXPMultiplier * fCheckPKPointResult(oCharID, PK_RESULT_XPPENALTY)
        local nXP = GetXP(oCharID)
        nXP = nXP + RoundDown(nXPGain)
        SetXP(oCharID, nXP)
        ALog(2, "2011 : CHAR=%u : XP=%u :: Player kills NPC and gains XP", oCharID, RoundDown(nXPGain))
      end
    end
    local nNPCScript = GetNPCScriptID(oTargetID)
    if nNPCScript and 80000 < nNPCScript then
      ExecuteScript(nNPCScript, 0, 0)
    end
  end
elseif nTarType == 1 and nCharType == 1 then
  local nAttribID = GetNPCAttrib(oTargetID)
  nTarClan = GetClanID(oTargetID)
  if nTarClan and 0 < nTarClan and nTarClan <= MAX_CLAN then
    local nClanType = GetClanType(nTarClan)
    if nClanType == nAttribID then
      SetClanType(nTarClan, 0)
      fChooseNewLeader(nTarClan)
    end
  end
elseif nTarType == 0 and nCharType == 1 then
  ALog(2, "2010 : CHAR=%u :: NPC kills player character", Target_id)
  if not bSelfKill then
    local nNameID = GetNameID(oCharID)
    if nNameID and nNameID ~= 0 then
      SendGenMessage(oTargetID, 1132, 1, nNameID)
    end
  end
  local nDrops = fDropAllUniqueItems(oTargetID, 0, 1)
  if 0 < nDrops then
    nResult = GetSceneZoneFlag(oTargetID)
    nTarClan, nTarGuild, nTarRank = GetAffiliation(oTargetID)
    if nResult and nTarRank and nResult == 1 and nTarRank ~= RANK_LEADER then
      SetZoneKillFlag(oTargetID, 1)
    end
    ExecuteScript(28733, oTargetID, 0)
  elseif fCheckPKPointResult(oTargetID, PK_RESULT_ITEMDROP) == 1 and 0 < fDropRandomItem(oTargetID) then
    SendGenMessage(oTargetID, 2132, 1)
  end
  SetPlayerScratchData(oTargetID, 1, 0)
  SetPlayerScratchData(oCharID, 31, oTargetID)
  ExecuteScript(28726)
end
