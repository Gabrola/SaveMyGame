<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class UpdateStatic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'replay:static';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update static data.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $css = '';

            $client = new Client();
            $res = $client->get('https://global.api.pvp.net/api/lol/static-data/na/v1.2/realm?api_key=' . env('RIOT_API_KEY'));

            $json = json_decode($res->getBody());

            $version = $json->dd;

            $ddURL = $json->cdn . '/' . $version;

            $res = $client->get('https://global.api.pvp.net/api/lol/static-data/na/v1.2/champion?champData=image&api_key=' . env('RIOT_API_KEY'));

            $json = json_decode($res->getBody(), true);

            $normalW = 48;
            $smallW = 36;
            $tinyW = 24;

            $static = [
                'champions' => [],
                'items'     => [],
                'spells'    => [],
                'version'   => $version
            ];

            foreach($json['data'] as $champion)
            {
                $x = -$champion['image']['x'];
                $y = -$champion['image']['y'];

                $smallX = ($x / $normalW) * $smallW;
                $smallY = ($y / $normalW) * $smallW;

                $tinyX = ($x / $normalW) * $tinyW;
                $tinyY = ($y / $normalW) * $tinyW;

                $static['champions'][$champion['id']] = $champion['name'];

                $url = $ddURL . '/img/sprite/' . $champion['image']['sprite'];
                $smallUrl = $ddURL . '/img/sprite/small_' . $champion['image']['sprite'];
                $tinyUrl = $ddURL . '/img/sprite/tiny_' . $champion['image']['sprite'];

                $css .= sprintf(".lol-champion-%d { background: url('%s') %dpx %dpx; }\n",
                    $champion['id'], $url, $x, $y);

                $css .= sprintf(".lol-small-champion-%d { background: url('%s') %dpx %dpx; }\n",
                    $champion['id'], $smallUrl, $smallX, $smallY);

                $css .= sprintf(".lol-tiny-champion-%d { background: url('%s') %dpx %dpx; }\n",
                    $champion['id'], $tinyUrl, $tinyX, $tinyY);
            }

            $res = $client->get('https://global.api.pvp.net/api/lol/static-data/na/v1.2/item?itemListData=image&api_key=' . env('RIOT_API_KEY'));

            $json = json_decode($res->getBody(), true);

            foreach($json['data'] as $item)
            {
                $x = -$item['image']['x'];
                $y = -$item['image']['y'];

                $smallX = ($x / $normalW) * $smallW;
                $smallY = ($y / $normalW) * $smallW;

                $tinyX = ($x / $normalW) * $tinyW;
                $tinyY = ($y / $normalW) * $tinyW;

                $static['items'][$item['id']] = $item['name'];

                $url = $ddURL . '/img/sprite/' . $item['image']['sprite'];
                $smallUrl = $ddURL . '/img/sprite/small_' . $item['image']['sprite'];
                $tinyUrl = $ddURL . '/img/sprite/tiny_' . $item['image']['sprite'];

                $css .= sprintf(".lol-item-%d { background: url('%s') %dpx %dpx; }\n",
                    $item['id'], $url, $x, $y);

                $css .= sprintf(".lol-small-item-%d { background: url('%s') %dpx %dpx; }\n",
                    $item['id'], $smallUrl, $smallX, $smallY);

                $css .= sprintf(".lol-tiny-item-%d { background: url('%s') %dpx %dpx; }\n",
                    $item['id'], $tinyUrl, $tinyX, $tinyY);
            }

            $res = $client->get('https://global.api.pvp.net/api/lol/static-data/euw/v1.2/summoner-spell?spellData=image&api_key=' . env('RIOT_API_KEY'));

            $json = json_decode($res->getBody(), true);

            foreach($json['data'] as $spell)
            {
                $x = -$spell['image']['x'];
                $y = -$spell['image']['y'];

                $smallX = ($x / $normalW) * $smallW;
                $smallY = ($y / $normalW) * $smallW;

                $tinyX = ($x / $normalW) * $tinyW;
                $tinyY = ($y / $normalW) * $tinyW;

                $static['spells'][$spell['id']] = $spell['name'];

                $url = $ddURL . '/img/sprite/' . $spell['image']['sprite'];
                $smallUrl = $ddURL . '/img/sprite/small_' . $spell['image']['sprite'];
                $tinyUrl = $ddURL . '/img/sprite/tiny_' . $spell['image']['sprite'];

                $css .= sprintf(".lol-summoner-%d { background: url('%s') %dpx %dpx; }\n",
                    $spell['id'], $url, $x, $y);

                $css .= sprintf(".lol-small-summoner-%d { background: url('%s') %dpx %dpx; }\n",
                    $spell['id'], $smallUrl, $smallX, $smallY);

                $css .= sprintf(".lol-tiny-summoner-%d { background: url('%s') %dpx %dpx; }\n",
                    $spell['id'], $tinyUrl, $tinyX, $tinyY);
            }

            \File::put(base_path('resources/assets/sass/sprites-generated.scss'), $css);
            \File::put(config_path('static.php'), '<?php return ' . var_export($static, true) . ';');
        }
        catch(\Exception $e){
            $this->error($e);
        }

        \Artisan::call('config:cache');
    }
}
