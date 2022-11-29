<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Github\GithubUser;
use App\Models\Github\Repo;

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
    public function handle(): int
    {
        $client = new \GuzzleHttp\Client();
        $bearer = config('app.github_bearer');
        $entryPoint = $this->getGithubEndpointInfo('https://api.github.com/users/hermansochi', $bearer);

        dd($entryPoint);

        if($entryPoint['StatusCode'] !== 200) {
            $this->error('StatusCode: ' . $entryPoint['StatusCode'] );
            return Command::FAILURE;
        }

        $githubUser = $this->saveGithubUsersToBD($entryPoint, 'root_user');

        $this->line('User ID: ' . $githubUser->github_id .
                    ' User name: ' . $githubUser->name .
                    ' Repos url: ' . $githubUser->repos_url);
        $repos = $this->getGithubEndpointInfo($githubUser->repos_url, $bearer);

        $reposResult = $this->saveGithubUserReposToBD($githubUser->id, $repos);
        dd($reposResult);

        $response = $client->request('GET', $obj->repos_url);
        $this->info('StatusCode: ' . $response->getStatusCode());
        $this->info('ContentType: ' . $response->getHeaderLine('content-type'));
        $this->info('X-ratelimit-limit: ' . $response->getHeaderLine('x-ratelimit-limit'));
        $this->info('X-ratelimit-remaining: ' . $response->getHeaderLine('x-ratelimit-remaining'));
        $this->info('X-ratelimit-used: ' . $response->getHeaderLine('x-ratelimit-used'));
        $epoch = $response->getHeaderLine('x-ratelimit-reset');
        $dt = new \DateTime("@$epoch");
        $this->info('X-ratelimit-reset: ' . $dt->format('Y-m-d H:i:s'));
        $body = $response->getBody();

        $obj = json_decode($body);

        foreach ($obj as $item) {
            $this->line('Repo id: ' . $item->id . ' ' . $item->full_name . ' Created at: ' . $item->created_at . ' Updated at: ' . $item->updated_at . ' Pushed at: ' . $item->pushed_at . ' Description: ' . $item->description);
            $response = $client->request('GET', 'https://api.github.com/repos/' . $item->full_name .'/collaborators', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $bearer
                ]
            ]);

            $this->info('StatusCode: ' . $response->getStatusCode());
            $this->info('ContentType: ' . $response->getHeaderLine('content-type'));
            $this->info('X-ratelimit-limit: ' . $response->getHeaderLine('x-ratelimit-limit'));
            $this->info('X-ratelimit-remaining: ' . $response->getHeaderLine('x-ratelimit-remaining'));
            $this->info('X-ratelimit-used: ' . $response->getHeaderLine('x-ratelimit-used'));
            $epoch = $response->getHeaderLine('x-ratelimit-reset');
            $dt = new \DateTime("@$epoch");
            $this->info('X-ratelimit-reset: ' . $dt->format('Y-m-d H:i:s'));

            $collaborators = json_decode($response->getBody());
            //dd($response, $obj);
            foreach ($collaborators as $collaborator) {
                $this->line('    collaborator: ' . $collaborator->id . ' ' . $collaborator->login);
            }

            $response = $client->request('GET', 'https://api.github.com/repos/' . $item->full_name .'/contributors', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $bearer
                ]
            ]);

            
            $this->info('StatusCode: ' . $response->getStatusCode());
            $this->info('ContentType: ' . $response->getHeaderLine('content-type'));
            $this->info('X-ratelimit-limit: ' . $response->getHeaderLine('x-ratelimit-limit'));
            $this->info('X-ratelimit-remaining: ' . $response->getHeaderLine('x-ratelimit-remaining'));
            $this->info('X-ratelimit-used: ' . $response->getHeaderLine('x-ratelimit-used'));
            $epoch = $response->getHeaderLine('x-ratelimit-reset');
            $dt = new \DateTime("@$epoch");
            $this->info('X-ratelimit-reset: ' . $dt->format('Y-m-d H:i:s'));

            $contributors = json_decode($response->getBody());
            //dd($response, $obj);
            foreach ($contributors as $contributor) {
                $this->line('    contributor: ' . $contributor->id . ' ' . $contributor->login);
                if ($contributor->login == 'hermansochi') { dd($contributor); }
            }

        }

        //dd($obj);
        return Command::SUCCESS;
    }

    /**
     * Get info from my github entrypoint.
     * 
     * @param string $url
     * @param string $bearer
     * @param int $page
     * @param int $per_page
     * @return array
     */
    private function getGithubEndpointInfo(
        string $url,
        string $bearer = null,
        int $page = 1,
        int $per_page = 30
        ): array
    {
        
        $client = new \GuzzleHttp\Client();
        $headers = [
            'headers' => [
                'Accept' => 'application/vnd.github+json'
            ],
            'query' => [
                'page' => $page,
                'per_page' => $per_page
            ]
        ];
        
        if (!is_null($bearer)) {
            $headers['headers']['Authorization'] = 'Bearer ' . $bearer;
        }
        $response = $client->request('GET', $url, $headers);
        $headers = [
            'StatusCode' => $response->getStatusCode(),
            'ContentType' => $response->getHeaderLine('content-type'),
            'X-ratelimit-limit' => $response->getHeaderLine('x-ratelimit-limit'),
            'X-ratelimit-remaining' => $response->getHeaderLine('x-ratelimit-remaining'),
            'X-ratelimit-used' => $response->getHeaderLine('x-ratelimit-used'),
        ];
        $epoch = $response->getHeaderLine('x-ratelimit-reset');
        $dt = new \DateTime("@$epoch");
        $headers['X-ratelimit-reset'] = $dt->format('Y-m-d H:i:s');

        return [
            'headers' => $headers,
            'body' => json_decode($response->getBody(), true)
        ];
    }

    /**
     * Save info from my github to BD.
     * 
     * @param array $collaborator
     * @param string $userRole
     * @return GithubUser
     */
    private function saveGithubUsersToBD(array $collaborator, string $userRole): GithubUser
    {
        $res = GithubUser::updateOrCreate(
            [
                'github_id' => $collaborator['id']
            ],
            [
                //$table->uuid('id')->primary();
                //$table->biginteger('github_id');
                'role' => $userRole,
                'avatar_url' => $collaborator['avatar_url'],
                'url' => $collaborator['url'],
                'html_url' => $collaborator['html_url'],
                'repos_url' => $collaborator['repos_url'],
                'type' => $collaborator['type'],
                'name' => $collaborator['name'],
                'company' => $collaborator['company'],
                'blog' => $collaborator['blog'],
                'location' => $collaborator['location'],
                'email' => $collaborator['email'],
                'hireable' => $collaborator['hireable'],
                'public_repos' => $collaborator['public_repos'],
                'public_gists' => $collaborator['public_gists'],
                'followers' => $collaborator['followers'],
                'following' => $collaborator['following'],
                'github_created_at' => $collaborator['created_at'],
                'github_updated_at' => $collaborator['updated_at'],

            ]
        );

        return $res;
    }

    /**
     * Save info from my github to BD.
     * 
     * @param string $uuid
     * @param array $repos
     * @return boolean
     */
    private function saveGithubUserReposToBD(string $uuid, array $repos): bool
    {
        
        foreach ($repos as $repo) {
            if (gettype($repo) === 'array') {
                $this->line($repo['id']);
                $this->line($repo['name']);
                $githubUser =  GithubUser::find($uuid);
                //dd($repo['id']);
                $res = $githubUser->repos()->updateOrCreate(
                    [
                        'github_id' => $repo['id'],
                    ],
                    [
                        //$table->uuid('id')->primary();
                        //$table->biginteger('github_id');

                        //'github_id' => $repo['id'],
                        'name' => $repo['name'],
                        'full_name' => $repo['full_name'],
                        'private' => $repo['private'],
                        'url' => $repo['url'],
                        'html_url' => $repo['html_url'],
                        'description' => $repo['description'],
                        'fork' => $repo['fork'],
                        'homepage' => $repo['homepage'],
                        'size' => $repo['size'],
                        'stargazers_count' => $repo['stargazers_count'],
                        'watchers_count' => $repo['watchers_count'],
                        'forks' => $repo['forks'],
                        'forks_count' => $repo['forks_count'],
                        'open_issues' => $repo['open_issues'],
                        'open_issues_count' => $repo['open_issues_count'],
                        'watchers' => $repo['watchers'],
                        'language' => $repo['language'],
                        'has_issues' => $repo['has_issues'],
                        'has_projects' => $repo['has_projects'],
                        'has_downloads' => $repo['has_downloads'],
                        'has_wiki' => $repo['has_wiki'],
                        'has_pages' => $repo['has_pages'],
                        'has_discussions' => $repo['has_discussions'],
                        'is_template' => $repo['is_template'],
                        'mirror_url' => $repo['mirror_url'],
                        'archived' => $repo['archived'],
                        'disabled' => $repo['disabled'],
                        'allow_forking' => $repo['allow_forking'],
                        'visibility' => $repo['visibility'],
                        'default_branch' => $repo['default_branch'],
                        'github_created_at' => $repo['created_at'],
                        'github_updated_at' => $repo['updated_at'],
                        'github_pushed_at'  => $repo['pushed_at']
                    ]
                );
            }
        }

        return true;
    }
}
