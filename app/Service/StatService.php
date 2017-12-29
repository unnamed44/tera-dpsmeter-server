<?php

namespace App\Service;

use App\Stat;

class StatService
{
    public function getByBossSince(\DateTimeInterface $since)
    {
        $stats = Stat::query()
            ->where('encounter_unix', '>=', $since->getTimestamp())
            ->get();

        $byBoss = [];

        $stats->each(function (Stat $stat) use (&$byBoss) {
            $key = $stat->area_id.'_'.$stat->boss_id;
            foreach ($stat->data->members as $member) {
                $member->stat = $stat;
                $byBoss[$key][] = $member;
            }
        });

        foreach ($byBoss as $key => $boss) {
            usort($boss, function ($a, $b) {
                return $b->playerDps - $a->playerDps;
            });
            $byBoss[$key] = collect($boss)->unique('playerName')->toArray();
        }

        return $byBoss;
    }
}
