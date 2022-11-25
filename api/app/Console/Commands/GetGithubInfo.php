<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GetGithubInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'github:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://api.github.com/users/hermansochi');
        $this->info('StatusCode' . $response->getStatusCode());
        $this->info('ContentType' . $response->getHeaderLine('content-type'));
        $body = $response->getBody();

        $obj = json_decode($body);
        $this->line('Repos url: ' . $obj->repos_url);

        $response = $client->request('GET', $obj->repos_url);
        $this->info('StatusCode' . $response->getStatusCode());
        $this->info('ContentType' . $response->getHeaderLine('content-type'));
        $body = $response->getBody();

        $obj = json_decode($body);

        foreach ($obj as $item) {
            $this->line('Repo id: ' . $item->id . ' ' . $item->full_name);
        }

        //dd($obj);
        return Command::SUCCESS;
    }
}
