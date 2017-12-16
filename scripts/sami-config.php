<?php

use Sami\RemoteRepository\GitHubRemoteRepository;
use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

$dir = realpath(__DIR__.'/../');
$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in($dir.'/src')
;
$versions = GitVersionCollection::create($dir);

if (!empty(getenv('_SAMI_BRANCH'))) {
    $versions->add(getenv('_SAMI_BRANCH'));
} else {
    $versions->addFromTags('1.1.*')
        ->add('master', 'master branch');
}
if (!empty(getenv('_SAMI_TEMPLATE_DIR'))) {
    $templatedir = getenv('_SAMI_TEMPLATE_DIR');
} else {
    $templatedir = $dir;
}

return new Sami($iterator, [
    'template_dirs' => [$templatedir],
    'theme' => 'sami_highlight',
    'title' => 'Rokka PHP Client API',
    'versions' => $versions,
    'build_dir' => $dir.'/sami-output/build/client-php-api/%version%',
    'cache_dir' => $dir.'/sami-output/cache/%version%',
    'remote_repository' => new GitHubRemoteRepository('rokka-io/rokka-client-php', $dir),
    'default_opened_level' => 2,
]);
