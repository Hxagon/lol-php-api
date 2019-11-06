<?php
require_once __DIR__  . '/vendor/autoload.php';

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
    $api = new LeagueAPI([LeagueAPI::SET_KEY => 'RGAPI-d0725846-c969-4040-aa2e-4fc5e2f4b36f', LeagueAPI::SET_REGION => Region::EUROPE_WEST]);
} catch (SettingsException $e) {
    echo $e->getMessage();
} catch (GeneralException $e) {
    echo $e->getMessage();
}


try {
    if (isset($_GET['summoners_name'])) {
        $summoner = $api->getSummonerByName($_GET['summoners_name']);
    }

} catch (RequestException $e) {
} catch (ServerException $e) {
} catch (ServerLimitException $e) {
} catch (SettingsException $e) {
} catch (GeneralException $e) {
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
    if (isset($summoner)) {
        $currentGame = $api->getCurrentGameInfo($summoner->id);
    }

} catch (RequestException $ex) {
    if ($ex->getCode() === 404) {
        $currentlyPlaying = false;
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
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="stylesheet" href="bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="bootstrap.min.css">
    <title>Lol - Spell Cooldown Display</title>
</head>
<body style="background-color: #1b1e21; color: gray">

<div class="container-fluid">
    <?php if (!isset($summoner) || !$summoner) { ?>
        <form class="form">
            <label>
                <input type="text" name="summoners_name" placeholder="Summoner Name" /> &nbsp; <input class="btn btn-info" type="submit" value="Start" />
            </label>
        </form>
    <?php } else { ?>
        <div class="card text-white bg-success mb-3" style="max-width: 20rem;">
            <div class="card-header">Summoner: <?php echo $summoner->name ?></div>
            <div class="card-body">
                <h4 class="card-title">Playerinfo:</h4>
                Icon: <img style="max-height: 25px;" src="<?php echo $profileIcon->attrs['src'] ?>" alt="ProfileIcon"><br />
                Level: <?php echo $summoner->summonerLevel ?><br />
                Currently playing: <br />
                <?php
                    if (!$currentlyPlaying) {
                        echo 'Player currently not playing';
                    } else { ?>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Key</th>
                                <th>Value</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <th><code>gameId</code></th>
                                <td><code><?=$g->gameId?></code></td>
                            </tr>
                            <tr>
                                <th><code>gameLength</code></th>
                                <td><code><?=$g->gameLength?></code></td>
                            </tr>
                            <tr>
                                <th><code>participants</code></th>
                                <td>
                                    <ul>
                                        <?php foreach ($g->participants as $p): ?>
                                            <li><?=$p->summonerName?> (team <code><?=$p->teamId?></code>)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    <?php
                    }
                ?>
            </div>
        </div>
    <?php } ?>
</div>
</body>
</html>
