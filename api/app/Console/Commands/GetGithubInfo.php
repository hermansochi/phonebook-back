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
    protected $signature = 'github:get {--force :  For full update commit history}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get stats from github.';

    /**
     * Github api entrypoint.
     *
     * @var string
     */
    protected string $entrypoint;


    /**
     * Github api bearer.
     *
     * @var string
     */
    protected string $bearer;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $client = new \GuzzleHttp\Client();
        $this->bearer = config('app.github_bearer');
        $this->entrypoint = config('app.github_entrypoint');

        $entryPoint = $this->getGithubEndpointInfo($this->entrypoint, $this->bearer);

        $this->line('Get entrypoint info.');

        if($entryPoint['headers']['StatusCode'] !== 200) {
            $this->error('StatusCode: ' . $entryPoint['headers']['StatusCode'] );
            return Command::FAILURE;
        }

        $this->line('Save entrypoint info to BD.');
        $githubUser = $this->saveGithubUserToBD($entryPoint['body'], 'root_user');
        $this->line('   ID: ' . $githubUser->github_id .
                    ' User name: ' . $githubUser->name .
                    ' Repos url: ' . $githubUser->repos_url .
                    (($githubUser->wasRecentlyCreated) ? ' created.' : ' updated.')
                );

        $this->line('Get repos info.');
        $page = 1;
        $per_page = 100;

        do {
            $repos = $this->getGithubEndpointInfo($githubUser->repos_url, $this->bearer, 'all', $page, $per_page);
            if($repos['headers']['StatusCode'] !== 200) {
                $this->error('StatusCode: ' . $repos['headers']['StatusCode'] );
                return Command::FAILURE;
            }

            if ($repos['headers']['X-ratelimit-remaining'] > 0) {
                $this->info('X-ratelimit-remaining: ' . $repos['headers']['X-ratelimit-remaining']);
            } else {
                $this->error('X-ratelimit-remaining: ' . $repos['headers']['X-ratelimit-remaining']);
                return Command::FAILURE;
            }
    
            $this->line('Save ' . count($repos['body']) . ' repos record to BD.');
            $ret = $this->saveGithubUserReposToBD($githubUser->id, $repos['body']);
            if ($ret === Command::FAILURE) {
                return Command::FAILURE;
            }
            $page++;
        } while (count($repos['body']) === $per_page);

        //dd($obj);
        return Command::SUCCESS;
    }

    /**
     * Get info from my github entrypoint.
     * 
     * @param string $url
     * @param string $bearer
     * @param string $type
     * @param int $page
     * @param int $per_page
     * @return array
     */
    private function getGithubEndpointInfo(
        string $url,
        string $bearer = null,
        string $type = null,
        int $page = 1,
        int $per_page = 30,
        string $since = null,
        ): array
    {
        
        $client = new \GuzzleHttp\Client();
        $headers = [
            'headers' => [
                'Accept' => 'application/vnd.github+json'
            ],
        ];
        
        if (!is_null($bearer)) {
            $headers['headers']['Authorization'] = 'Bearer ' . $bearer;
        }
        if ($page !== 1) {
            $headers['query']['page'] = $page;
        }
        if ($per_page !== 30) {
            $headers['query']['per_page'] = $per_page;
        }
        if (!is_null($type)) {
            $headers['query']['type'] = $type;
        }
        if (!is_null($since)) {
            $headers['query']['since'] = $since;
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
     * @param array $githubUser
     * @param string $userRole
     * @return GithubUser
     */
    private function saveGithubUserToBD(array $githubUser, string $userRole): GithubUser
    {
        $res = GithubUser::updateOrCreate(
            [
                'github_id' => $githubUser['id']
            ],
            [
                'role' => $userRole,
                'login' => $githubUser['login'],
                'avatar_url' => $githubUser['avatar_url'],
                'url' => $githubUser['url'],
                'html_url' => $githubUser['html_url'],
                'repos_url' => $githubUser['repos_url'],
                'type' => $githubUser['type'],
                'site_admin' => $githubUser['site_admin'],
                'name' => $githubUser['name'],
                'company' => $githubUser['company'],
                'blog' => $githubUser['blog'],
                'location' => $githubUser['location'],
                'email' => $githubUser['email'],
                'hireable' => $githubUser['hireable'],
                'public_repos' => $githubUser['public_repos'],
                'public_gists' => $githubUser['public_gists'],
                'followers' => $githubUser['followers'],
                'following' => $githubUser['following'],
                'github_created_at' => $githubUser['created_at'],
                'github_updated_at' => $githubUser['updated_at'],

            ]
        );
        return $res;
    }

    /**
     * Save info from my github to BD.
     * 
     * @param string $uuid
     * @param array $repos
     * @return int
     */
    private function saveGithubUserReposToBD(string $uuid, array $repos): int
    {
        foreach ($repos as $repo) {
            $this->line('   Repo ID: ' . $repo['id'] . ' repo name: ' . $repo['name']);
            $githubUser = GithubUser::find($uuid);

            if (!$this->option('force')) {
                // check new repo
                $oldRepo = $githubUser->repos()->where('github_id', '=', $repo['id'])->first();
                if (is_null($oldRepo)) {
                    $previousPushDate = null;
                } else {
                    $previousPushDate = (new \Carbon\Carbon($oldRepo->github_pushed_at))->toIso8601String();
                }
            } else {
                $previousPushDate = null;
            }

            $res = $githubUser->repos()->updateOrCreate(
                [
                    'github_id' => $repo['id'],
                ],
                [
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

            //Save contributors for each repo
            $contributor_page = 1;
            $contributor_per_page = 100;
            do {
                $contributors = $this->getGithubEndpointInfo(
                    $repo['contributors_url'],
                    $this->bearer,
                    NULL,
                    $contributor_page,
                    $contributor_per_page
                );
                if($contributors['headers']['StatusCode'] !== 200) {
                    $this->error('StatusCode: ' . $contributors['headers']['StatusCode'] );
                    return Command::FAILURE;
                }
    
                if ($contributors['headers']['X-ratelimit-remaining'] > 0) {
                    $this->info('   X-ratelimit-remaining: ' .
                        $contributors['headers']['X-ratelimit-remaining']);
                } else {
                    $this->error('   X-ratelimit-remaining: ' .
                        $contributors['headers']['X-ratelimit-remaining']);
                    return Command::FAILURE;
                }

                $this->line('      Save ' . count($contributors['body']) . ' contributors record to BD.');
                $this->saveRepoContributorsToBD($repo['id'], $contributors['body']);

                $contributor_page++;
            } while (count($contributors['body']) === $contributor_per_page);

            //Save collaborators for each repo
            $collaborator_page = 1;
            $collaborator_per_page = 100;
            do {
                $collaborators = $this->getGithubEndpointInfo(
                    mb_substr($repo['collaborators_url'], 0, -15),
                    $this->bearer,
                    NULL,
                    $collaborator_page,
                    $collaborator_per_page
                );
                if($collaborators['headers']['StatusCode'] !== 200) {
                    $this->error('StatusCode: ' . $collaborators['headers']['StatusCode'] );
                    return Command::FAILURE;
                }
    
                if ($collaborators['headers']['X-ratelimit-remaining'] > 0) {
                    $this->info('   X-ratelimit-remaining: ' .
                        $collaborators['headers']['X-ratelimit-remaining']);
                } else {
                    $this->error('   X-ratelimit-remaining: ' .
                        $collaborators['headers']['X-ratelimit-remaining']);
                    return Command::FAILURE;
                }

                $this->line('      Save ' . count($collaborators['body']) . ' collaborators record to BD.');
                $this->saveRepoCollaboratorsToBD($repo['id'], $collaborators['body']);

                $collaborator_page++;
            } while (count($collaborators['body']) === $collaborator_per_page);

            //Save commits for each repo
            if ($previousPushDate === (new \Carbon\Carbon($res->github_pushed_at))->toIso8601String()) {
                $this->info('      No new push, skip commits loading.');
            } else {
                $commit_page = 1;
                $commit_per_page = 100;
                do {
                    $commits = $this->getGithubEndpointInfo(
                        mb_substr($repo['commits_url'], 0, -6),
                        $this->bearer,
                        NULL,
                        $commit_page,
                        $commit_per_page,
                        $since = $previousPushDate
                    );
                    if($commits['headers']['StatusCode'] !== 200) {
                        $this->error('StatusCode: ' . $commits['headers']['StatusCode'] );
                        return Command::FAILURE;
                    }
        
                    if ($commits['headers']['X-ratelimit-remaining'] > 0) {
                        $this->info('   X-ratelimit-remaining: ' .
                            $commits['headers']['X-ratelimit-remaining']);
                    } else {
                        $this->error('   X-ratelimit-remaining: ' .
                            $commits['headers']['X-ratelimit-remaining']);
                        return Command::FAILURE;
                    }

                    $this->line('      Save ' . count($commits['body']) . ' collaborators record to BD.');
                    $this->saveRepoCommitsToBD($repo['id'], $commits['body']);

                    $commit_page++;
                } while (count($commits['body']) === $commit_per_page);
            }
        }
        return Command::SUCCESS;
    }

    /**
     * Save info from my github to BD.
     * 
     * @param string $repoId
     * @param array $contributors
     * @return void
     */
    private function saveRepoContributorsToBD(string $repoId, array $contributors): void
    {
        
        foreach ($contributors as $contributor) {
            $this->line(
                '      contributor ID: ' . $contributor['id'] .
                ' contributor login: ' . $contributor['login']
            );
            $repo =  Repo::where('github_id', '=', $repoId)->first();
            $res = $repo->contributors()->updateOrCreate(
                [
                    'github_id' => $contributor['id'],
                ],
                [
                    'login' => $contributor['login'],
                    'avatar_url' => $contributor['avatar_url'],
                    'url' => $contributor['url'],
                    'html_url' => $contributor['html_url'],
                    'repos_url' => $contributor['repos_url'],
                    'type' => $contributor['type'],
                    'site_admin' => $contributor['site_admin'],
                    'contributions' => $contributor['contributions']
                ]
            );

        }
    }

    /**
     * Save info from my github to BD.
     * 
     * @param string $repoId
     * @param array $collaborators
     * @return void
     */
    private function saveRepoCollaboratorsToBD(string $repoId, array $collaborators): void
    {
        
        foreach ($collaborators as $collaborator) {
            $this->line(
                '      collaborator ID: ' . $collaborator['id'] .
                ' collaborator login: ' . $collaborator['login']
            );
            $repo =  Repo::where('github_id', '=', $repoId)->first();
            $res = $repo->collaborators()->updateOrCreate(
                [
                    'github_id' => $collaborator['id'],
                ],
                [
                    'login' => $collaborator['login'],
                    'avatar_url' => $collaborator['avatar_url'],
                    'url' => $collaborator['url'],
                    'html_url' => $collaborator['html_url'],
                    'repos_url' => $collaborator['repos_url'],
                    'type' => $collaborator['type'],
                    'site_admin' => $collaborator['site_admin'],
                    'permissions' => $collaborator['permissions'],
                    'role_name' => $collaborator['role_name']
                ]
            );

        }
    }

    /**
     * Save info from my github to BD.
     * 
     * @param string $repoId
     * @param array $collaborators
     * @return void
     */
    private function saveRepoCommitsToBD(string $repoId, array $commits): void
    {
        
        foreach ($commits as $commit) {
            $this->line(
                '      commit SHA: ' . $commit['sha'] .
                ' commit message: ' . $commit['commit']['message']
            );
            $repo =  Repo::where('github_id', '=', $repoId)->first();
            $res = $repo->commits()->updateOrCreate(
                [
                    'sha' => $commit['sha'],
                ],
                [
                    'author_id' => $commit['author']['id'],
                    'author_login' => $commit['author']['login'],
                    'author_name' => $commit['commit']['author']['name'],
                    'author_date' => $commit['commit']['author']['date'],
                    'committer_id' => $commit['committer']['id'],
                    'committer_login' => $commit['committer']['login'],
                    'committer_name' => $commit['commit']['committer']['name'],
                    'committer_date' => $commit['commit']['committer']['date'],
                    'message' => mb_substr($commit['commit']['message'], 0, 255)
                ]
            );

        }
    }
}
