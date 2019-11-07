<?php
require_once __DIR__ . '/vendor/autoload.php';

use RiotAPI\LeagueAPI\Exceptions\DataNotFoundException;
use RiotAPI\LeagueAPI\Exceptions\ForbiddenException;
use RiotAPI\LeagueAPI\Exceptions\GeneralException;
use RiotAPI\LeagueAPI\Exceptions\RequestException;
use RiotAPI\LeagueAPI\Exceptions\ServerException;
use RiotAPI\LeagueAPI\Exceptions\ServerLimitException;
use RiotAPI\LeagueAPI\Exceptions\SettingsException;
use RiotAPI\LeagueAPI\LeagueAPI;
use RiotAPI\LeagueAPI\Definitions\Region;
use RiotAPI\DataDragonAPI\DataDragonAPI;

//  Initialize the library
try {
    $api = new LeagueAPI([
        LeagueAPI::SET_KEY => 'RGAPI-bfd68670-78df-4c7f-80f0-90a252737af5',
        LeagueAPI::SET_REGION => Region::EUROPE_WEST
    ]);
} catch (SettingsException $e) {
    echo $e->getMessage();
} catch (GeneralException $e) {
    echo $e->getMessage();
}


try {
    if (isset($_POST['summoners_name'])) {
        $summoner = $api->getSummonerByName($_POST['summoners_name']);
    }

    if (isset($e) && $e instanceof ForbiddenException) {
        echo '<h1>API KEY EXPIRED</h1>';
    }
} catch (RequestException $e) {
    if ($e instanceof ForbiddenException) {
        die('API KEY EXPIRED');
    }

    if ($e instanceof DataNotFoundException) {
        echo '<script>alert("' . $e->getMessage() . '")</script>';
    }

} catch (ServerException $e) {
    die('ServerException');
} catch (ServerLimitException $e) {
    die('ServerLimitException');
} catch (SettingsException $e) {
    die('SettingsException');
} catch (GeneralException $e) {
    die('GeneralException');
}

// Get Summoner
try {
    if (isset($summoner)) {
        DataDragonAPI::initByCdn();
    }
} catch (\RiotAPI\DataDragonAPI\Exceptions\RequestException $e) {
}

// Get profile pic
try {
    if (isset($summoner)) {
        $profileIcon = DataDragonAPI::getProfileIcon($summoner->profileIconId, ['class' => 'rounded-circle',]);
    }
} catch (\RiotAPI\DataDragonAPI\Exceptions\SettingsException $e) {
}

// Get current game
try {
    $currentlyPlaying = false;

    if (isset($summoner)) {
        $currentGame = $api->getCurrentGameInfo($summoner->id);
        $currentlyPlaying = true;
    }

    if (!$currentlyPlaying) {
        echo '<script>alert("Looks like Summoner is not playing ATM")</script>';
    }

} catch (RequestException $ex) {
    if ($ex->getCode() === 404) {
        $currentlyPlaying = false;
    }

    if (!$currentlyPlaying) {
        echo '<script>alert("Looks like Summoner is not playing ATM")</script>';
    }

} catch (ServerException $e) {
} catch (ServerLimitException $e) {
} catch (SettingsException $e) {
} catch (GeneralException $e) {
}
?>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <link rel="stylesheet" href="bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="bootstrap.min.css">
    <title>Lol - Spell Cooldown Display</title>
</head>
<body style="background-color: #1b1e21; color: gray">

<nav class="navbar navbar-expand-sm navbar-dark bg-dark" style="margin-bottom: 25px;">
    <a class="navbar-brand" href="#">LoL - Cooldown Spy</a>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <form class="form-inline my-2 my-lg-0" method="post">
            <input class="form-control mr-sm-2" name="summoners_name" type="text" placeholder="Summoner Name" aria-label="Search">
            <input class="btn btn-info" type="submit" value="Start"/>
        </form>
    </div>
</nav>

<div class="container-fluid">

    <?php if (isset($currentlyPlaying) && $currentlyPlaying) {
        $gameStartTime = $currentGame->gameStartTime / 1000;
        $gameStartTime = date('H:i:s - d.m.Y', $gameStartTime);
    ?>
    <span class="badge badge-info"><h5>Mode: <?=$currentGame->gameMode?></h5></span>
    <span class="badge badge-info"><h5>Id: <?=$currentGame->gameId?></h5></span>
    <span class="badge badge-info"><h5>Starttime: <?=$gameStartTime?></h5></span>
    <span class="badge badge-info"><h5>Length: <?=$currentGame->gameLength?><small>(seconds)</small></h5></span>
    <hr >
    <div class="card-group">
        <?php
        $count = 0;
        $players = count($currentGame->participants);
        foreach ($currentGame->participants as $participant) {
            $count++;
            $champion = $api->getStaticChampion($participant->championId);
            $championIcon = DataDragonAPI::getChampionIconUrl($champion->name);
            $participantName = $participant->summonerName;
            $perkCount = count($participant->perks->perkIds);
            $spell1 = $participant->spell1Id;
            $spell2 = $participant->spell2Id;

            if ($count === ($players / 2) + 1) {
                echo '<div style="width: 100%; height: 15px; clear: both">.</div>';
            }
        ?>

        <div class="card">
            <img width="120" height="120" style="max-width: 120px; max-height: 120px;" src="<?=$championIcon?>" class="card-img-top" alt="<?=$champion->name?>">
            <div class="card-body">
                <h6 class="card-title">Player: <?=$participant->summonerName?></h6>
                <h6 class="card-title">Champion: <?=$champion->name?></h6>

                    <ol>
                        <li>Bot/Human:
                        <?php if ($participant->bot): ?>
                            <span class="badge badge-pill badge-danger">BOT</span>
                        <?php else: ?>
                            <span class="badge badge-pill badge-success">HUMAN</span>
                        <?php endif; ?>
                        </li>
                        <li>Perks: <?=$perkCount?></li>
                        <li>Spell 1 Id: <?=$spell1?></li>
                        <li>Spell 2 Id: <?=$spell2?></li>
                    </ol>

            </div>
            <div class="card-footer">
                <small class="text-muted">Some additional info</small>
            </div>
        </div>

        <?php } ?>
    </div>
    <?php }?>
</div>
</body>
</html>
