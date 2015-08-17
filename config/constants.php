<?php
return [
    'queues'    =>  [
        'CUSTOM'                    => 'Custom Game',
        'NORMAL_5x5_BLIND'          => 'Normal 5v5',
        'BOT_5x5'                   => 'Coop vs AI',
        'BOT_5x5_INTRO'             => 'Coop vs AI Intro',
        'BOT_5x5_BEGINNER'          => 'Coop vs AI Beginner',
        'BOT_5x5_INTERMEDIATE'      => 'Coop vs AI Intermediate',
        'NORMAL_3x3'                => 'Normal 3v3',
        'NORMAL_5x5_DRAFT'          => 'Normal 5v5 Draft',
        'ODIN_5x5_BLIND'            => 'Dominion',
        'ODIN_5x5_DRAFT'            => 'Dominion Draft',
        'BOT_ODIN_5x5'              => 'Coop vs AI Dominion',
        'RANKED_SOLO_5x5'           => 'Ranked Solo 5v5',
        'RANKED_PREMADE_3x3'        => 'Ranked Premade 3v3',
        'RANKED_PREMADE_5x5'        => 'Ranked Premade 5v5',
        'RANKED_TEAM_3x3'           => 'Ranked Team 3v3',
        'RANKED_TEAM_5x5'           => 'Ranked Team 5v5',
        'BOT_TT_3x3'                => 'Coop vs AI 3v3',
        'GROUP_FINDER_5x5'          => 'Team Builder 5v5',
        'ARAM_5x5'                  => 'ARAM',
        'ONEFORALL_5x5'             => 'One for All',
        'FIRSTBLOOD_1x1'            => 'Snowdown Showdown 1v1',
        'FIRSTBLOOD_2x2'            => 'Snowdown Showdown 2v2',
        'SR_6x6'                    => 'Hexakill',
        'URF_5x5'                   => 'URF',
        'BOT_URF_5x5'               => 'Coop vs AI URF',
        'NIGHTMARE_BOT_5x5_RANK1'   => 'Doom Bots Rank 1',
        'NIGHTMARE_BOT_5x5_RANK2'   => 'Doom Bots Rank 2',
        'NIGHTMARE_BOT_5x5_RANK5'   => 'Doom Bots Rank 5',
        'ASCENSION_5x5'             => 'Ascension',
        'HEXAKILL'                  => 'Hexakill',
        'KING_PORO_5x5'             => 'King Poro',
        'COUNTER_PICK'              => 'Nemesis',
        'BILGEWATER_ARAM_5x5'       => 'Butcher\'s Bridge ARAM',
        'BILGEWATER_5x5'            => 'Black Market Brawlers'
    ],

    'queueIds'   => [
        0   => 'Custom Game',
        2   => 'Normal 5v5',
        7   => 'Coop vs AI',
        31  => 'Coop vs AI Intro',
        32  => 'Coop vs AI Beginner',
        33  => 'Coop vs AI Intermediate',
        8   => 'Normal 3v3',
        14  => 'Normal 5v5 Draft',
        16  => 'Dominion',
        17  => 'Dominion Draft',
        25  => 'Coop vs AI Dominion',
        4   => 'Ranked Solo 5v5',
        9   => 'Ranked Premade 3v3',
        6   => 'Ranked Premade 5v5',
        41  => 'Ranked Team 3v3',
        42  => 'Ranked Team 5v5',
        52  => 'Coop vs AI 3v3',
        61  => 'Team Builder 5v5',
        65  => 'ARAM',
        70  => 'One for All',
        72  => 'Snowdown Showdown 1v1',
        73  => 'Snowdown Showdown 2v2',
        75  => 'Hexakill',
        76  => 'URF',
        83  => 'Coop vs AI URF',
        91  => 'Doom Bots Rank 1',
        92  => 'Doom Bots Rank 2',
        93  => 'Doom Bots Rank 5',
        96  => 'Ascension',
        98  => 'Hexakill',
        300 => 'King Poro',
        310 => 'Nemesis',
        100 => 'Butcher\'s Bridge ARAM',
        313 => 'Black Market Brawlers'
    ],

    'queueIdToType' => [
        0	=> 'CUSTOM',
        8	=> 'NORMAL_3x3',
        2	=> 'NORMAL_5x5_BLIND',
        14	=> 'NORMAL_5x5_DRAFT',
        4	=> 'RANKED_SOLO_5x5',
        6	=> 'RANKED_PREMADE_5x5*',
        9	=> 'RANKED_PREMADE_3x3*',
        41	=> 'RANKED_TEAM_3x3',
        42	=> 'RANKED_TEAM_5x5',
        16	=> 'ODIN_5x5_BLIND',
        17	=> 'ODIN_5x5_DRAFT',
        7	=> 'BOT_5x5*',
        25	=> 'BOT_ODIN_5x5',
        31	=> 'BOT_5x5_INTRO',
        32	=> 'BOT_5x5_BEGINNER',
        33	=> 'BOT_5x5_INTERMEDIATE',
        52	=> 'BOT_TT_3x3',
        61	=> 'GROUP_FINDER_5x5',
        65	=> 'ARAM_5x5',
        70	=> 'ONEFORALL_5x5',
        72	=> 'FIRSTBLOOD_1x1',
        73	=> 'FIRSTBLOOD_2x2',
        75	=> 'SR_6x6',
        76	=> 'URF_5x5',
        83	=> 'BOT_URF_5x5',
        91	=> 'NIGHTMARE_BOT_5x5_RANK1',
        92	=> 'NIGHTMARE_BOT_5x5_RANK2',
        93	=> 'NIGHTMARE_BOT_5x5_RANK5',
        96	=> 'ASCENSION_5x5',
        98	=> 'HEXAKILL',
        100	=> 'BILGEWATER_ARAM_5x5',
        300	=> 'KING_PORO_5x5',
        310	=> 'COUNTER_PICK',
        313	=> 'BILGEWATER_5x5'
    ],

    'batfile'   => '@echo off
setlocal ENABLEEXTENSIONS
setlocal EnableDelayedExpansion

set VALUE_NAME=LocalRootFolder

set KEY_NAME=HKLM\SOFTWARE\Wow6432Node\Riot Games\RADS
FOR /F "tokens=2*" %%%%A IN (\'REG.EXE QUERY "%%KEY_NAME%%" /v "%%VALUE_NAME%%" 2^>NUL ^| FIND "REG_SZ"\') DO SET RADS=%%%%B
IF NOT "!RADS!"=="" GOTO PLAY

set KEY_NAME=HKCU\SOFTWARE\Riot Games\RADS
FOR /F "tokens=2*" %%%%A IN (\'REG.EXE QUERY "%%KEY_NAME%%" /v "%%VALUE_NAME%%" 2^>NUL ^| FIND "REG_SZ"\') DO SET RADS=%%%%B
IF NOT "!RADS!"=="" GOTO PLAY

set KEY_NAME=HKCU\Software\Classes\VirtualStore\MACHINE\SOFTWARE\Wow6432Node\Riot Games\RADS
FOR /F "tokens=2*" %%%%A IN (\'REG.EXE QUERY "%%KEY_NAME%%" /v "%%VALUE_NAME%%" 2^>NUL ^| FIND "REG_SZ"\') DO SET RADS=%%%%B
IF NOT "!RADS!"=="" GOTO PLAY

set KEY_NAME=HKCU\Software\Classes\VirtualStore\MACHINE\SOFTWARE\Riot Games\RADS
FOR /F "tokens=2*" %%%%A IN (\'REG.EXE QUERY "%%KEY_NAME%%" /v "%%VALUE_NAME%%" 2^>NUL ^| FIND "REG_SZ"\') DO SET RADS=%%%%B
IF NOT "!RADS!"=="" GOTO PLAY

IF EXIST "C:\Riot Games\League of Legends\RADS" DO
SET RADS=C:\Riot Games\League of Legends\RADS
GOTO PLAY

GOTO NOTFOUND

:PLAY

cd /D %%RADS%%
cd .\solutions\lol_game_client_sln\releases\
FOR /f %%%%i in (\'dir /a:d /b\') do set RELEASE=%%%%i
cd .\%%RELEASE%%\deploy

@start "" "League of Legends.exe" "8394" "LoLLauncher.exe" "" "%s %s:80 %s %d %s"
GOTO DONE

:NOTFOUND

echo Could not find League of Legends installation.
@pause

:DONE
endlocal',

    'windowsCommand'  => 'powershell clear;if(Get-Process \"LolClient\" -ErrorAction SilentlyContinue){$ErrorActionPreference=\"Stop\";$c=New-Object Net.Sockets.TcpClient;$c.Connect(\"127.0.0.1\",8393);$c.GetStream().write((%s),0,%u);Exit;}\"Error. Please make sure your LoL client is running.\";',

    'macCommand'  => "echo -e '%s' | nc 127.0.0.1 8393",
];