<?php
declare(strict_types=1);

namespace arie\reddust\block\utils;

use pocketmine\block\utils\RecordType;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

final class JukeboxUtils{
    public const DISC_NONE_POWER = 0;
    public const DISC_13_POWER = 1;
    public const DISC_CAT_POWER = 2;
    public const DISC_BLOCKS_POWER = 3;
    public const DISC_CHIRP_POWER = 4;
    public const DISC_FAR_POWER = 5;
    public const DISC_MALL_POWER = 6;
    public const DISC_MELLOHI_POWER = 7;
    public const DISC_STAL_POWER = 8;
    public const DISC_STRAD_POWER = 9;
    public const DISC_WARD_POWER = 10;
    public const DISC_11_POWER = 11;
    public const DISC_WAIT_POWER = 12;
    public const DISC_PIGSTEP_POWER = 13;
    public const DISC_OTHERSIDE_POWER = 14;

    public const RECORD_PIGSTEP = -1;
    public const RECORD_OTHERSIDE = -1;

    public static function getSignalStrength(RecordType $recordType) : int{
        return match($recordType->getSoundId()) {
            LevelSoundEvent::RECORD_13 => self::DISC_13_POWER,
            LevelSoundEvent::RECORD_CAT => self::DISC_CAT_POWER,
            LevelSoundEvent::RECORD_CHIRP => self::DISC_CHIRP_POWER,
            LevelSoundEvent::RECORD_FAR => self::DISC_FAR_POWER,
            LevelSoundEvent::RECORD_MALL => self::DISC_MALL_POWER,
            LevelSoundEvent::RECORD_MELLOHI => self::DISC_MELLOHI_POWER,
            LevelSoundEvent::RECORD_STAL => self::DISC_STAL_POWER,
            LevelSoundEvent::RECORD_STRAD => self::DISC_STRAD_POWER,
            LevelSoundEvent::RECORD_WARD => self::DISC_WARD_POWER,
            LevelSoundEvent::RECORD_11 => self::DISC_11_POWER,
            LevelSoundEvent::RECORD_WAIT => self::DISC_WAIT_POWER,
            self::RECORD_PIGSTEP => self::DISC_PIGSTEP_POWER,
            self::RECORD_OTHERSIDE => self::DISC_OTHERSIDE_POWER,
            default => self::DISC_NONE_POWER,
        };
    }
}