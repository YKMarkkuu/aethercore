<?php

namespace App\Enums;

enum SpacePermission: string
{
    case MANAGE_CHANNELS = 'manage_channels';
    case MANAGE_ROLES = 'manage_roles';
    case KICK_MEMBERS = 'kick_members';
    case BAN_MEMBERS = 'ban_members';
    case DELETE_MESSAGES = 'delete_messages';
    case MENTION_EVERYONE = 'mention_everyone';
    case MANAGE_SPACE = 'manage_space';
    case MANAGE_MESSAGES = 'manage_messages';

    /**
     * All permission values, e.g. for seeding an "Owner" role's
     * permissions array or validating an incoming request.
     */
    public static function all(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
