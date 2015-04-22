# PHP Git Repo

Manage a Git repository with PHP.
Provide an object oriented wrapper to run any Git command.

## Requirements

- PHP >= 5.2 (PHP 5.3 works fine)
- Git >= 1.5
- Hg >= 3.3.2

## Instantiate a PHPGit

    $repo = new PHPGit('/path/to/the/git/repo');

It does NOT create a Git repo, but a PHP object to manipulate an existing Git repo.

## Create a Git or HG repository

If the Git repository does not exist yet on filesystem, PHPGit can create it for you.

    $repo = PHPGit::create('/path/to/the/git/repo');

It runs `git init` and returns a PHPGit object.

If the Hg repository does not exist yet on filesystem, PHPHg can create it for you.

    $repo = PHPHg::create('/path/to/the/hg/repo');

It runs `hg init` and returns a PHPGit object.

## Run git/hg commands

git/hg commands can be run with the same syntax as in the CLI. Some examples:

    // change current branch to master
    $repo->git('checkout master');
    $repo->hg('up master')

    // pull from a remote
    $repo->git('pull origin master');
    $repo->hg('pull origin master')

    // add a remote repo
    $repo->git('remote add origin git://github.com/ornicar/php-git-repo.git');

There is no limitation, you can run any git/hg command.

The git()/hg() method returns the output string:

    echo $repo->git('log --oneline');

    e30b70b Move test repo to system tmp dir, introduce Command
    01fabb1 Add test repo
    12a95e6 Add base class with basic unit test
    58e7769 Fix readme
    c14c9ec Initial commit
    
    echo $repo->hg('status');
	
	? README.md
	M test.php	
	

The git()/hg() method throws a GitRuntimeException if the command is invalid:

    $repo->git('wtf'); // this git command does NOT exist: throw GitRuntimeException
	$repos->hg('wtf'); // this git command does NOT exist: throw HgRuntimeException

## Get branches informations

Some shortcut methods are provided to deal with branches in a convenient way.

### Get the branches list:
//For now, only git have commands for Branch

    $branches = $repo->getBranches();
    // returns array('master', 'other_branch')

### Get the current branch:

    $branch = $repo->getCurrentBranch();
    // returns 'master'

### Know if the repo has a given branch:

    $hasBranch = $repo->hasBranch('master');
    // returns true

## Get tags informations
//For now, only git have commands for Tag
### Get the tags list:

    $tags = $repo->getTags();
    // returns array('first_release', 'v2')

## Get commits informations

You can get an array of the last commits on the current branch.

    $commits = $repo->getCommits(15);
    // returns an array of the 15 last commits

Internally, this methods run `git log` with formatted output. The return value should look like:

    Array
    (
        [0] => Array
            (
                [id] => affb0e84a11b4180b0fa0e5d36bdac73584f0d71
                [tree] => 4b825dc642cb6eb9a060e54bf8d69288fbee4904
                [author] => Array
                    (
                        [name] => ornicar
                        [email] => myemail@gmail.com
                    )

                [authored_date] => 2010-09-22 19:17:35 +0200
                [commiter] => Array
                    (
                        [name] => ornicar
                        [email] => myemail@gmail.com
                    )

                [committed_date] => 2010-09-22 19:17:35 +0200
                [message] => My commit message
            )

        [1] => Array
            (
                ...

The first commit is the more recent one.

Internally, this methods run `hg log -l` with formatted output. The return value should look like:
	String("
	changeset:   113:87c310edec6f
	tag:         tip
	user:        Blondeau Gabriel <blondeau.gabriel@gmail.com>
	date:        Wed Apr 22 08:17:38 2015 +0000
	summary:     README.md edited online with Bitbucket

	changeset:   112:281ec79f27cc
	user:        Blondeau Gabriel <blondeau.gabriel@gmail.com>
	date:        Wed Apr 22 08:16:50 2015 +0000
	summary:     test.php edited online with Bitbucket

	changeset:   111:0588a1ea0385
	user:        Blondeau Gabriel <blondeau.gabriel@gmail.com>
	date:        Wed Apr 22 08:14:21 2015 +0000
	summary:     test.php edited online with Bitbucket ...")

## Debug mode

`PHPGit` / `PHPHg` constructors second parameter lets you enable debug mode.
When debug mode is on, commands and their output are displayed.

    $repo = new PHPGit('/path/to/the/git/repo', true);
	$repo = new PHPHg('/path/to/the/hg/repo', true);
	
## Configure

`PHPGit` / `PHPHg` can be configured by passing an array of options to the constructor third parameter.

### Change git executable path

You may need to provide the path to the git executable.

    $repo = new PHPGit('/path/to/the/git/repo', false, array('git_executable' => '/usr/bin/git'));
	$repo = new PHPHg('/path/to/the/hg/repo', false, array('hg_executable' => '/usr/bin/hg'));

On most Unix system, it's `/usr/bin/git`. On Windows, it may be `C:\Program Files\Git\bin`.
On most Unix system, it's `/usr/bin/hg`. On Windows, it may be `C:\Program Files\Hg\bin`. //I use tortoise so : `C:\Program Files\TortoiseHg\hg`

### Change the command class

By default, `PHPGit` / `PHPHg` will use `Command` class to implement Git commands.
By replacing this option, you can use your own command implementation:

    $repo = new PHPGit('/path/to/the/git/repo', false, array('command_class' => 'myGitCommand'));
	$repo = new PHPHg('/path/to/the/hg/repo', false, array('command_class' => 'myHgCommand'));

## Run test suite

All code is fully unit tested. To run tests on your server, from a CLI, run

    php /path/to/php-git-repo/prove.php
