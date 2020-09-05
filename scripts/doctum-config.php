<?php

use Doctum\RemoteRepository\GitHubRemoteRepository;
use Doctum\Doctum;
use Doctum\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

$dir = realpath(__DIR__.'/../');
$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in($dir.'/src')
;
$versions = GitVersionCollection::create($dir);

if (!empty(getenv('_DOCTUM_BRANCH'))) {
    $versions->add(getenv('_DOCTUM_BRANCH'));
} else {
    $versions->addFromTags('1.*.*')
        ->add('master', 'master branch');
}
if (!empty(getenv('_DOCTUM_TEMPLATE_DIR'))) {
    $templatedir = getenv('_DOCTUM_TEMPLATE_DIR');
} else {
    $templatedir = $dir;
}

return new Doctum($iterator, [
    'template_dirs' => [$templatedir],
    'theme' => 'doctum_highlight',
    'title' => 'Rokka PHP Client API',
    'versions' => $versions,
    'build_dir' => $dir.'/doctum-output/build/client-php-api/%version%',
    'cache_dir' => $dir.'/doctum-output/cache/%version%',
    'remote_repository' => new GitHubRemoteRepository('rokka-io/rokka-client-php', $dir),
]);
