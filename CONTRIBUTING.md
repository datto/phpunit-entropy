# Contributing to PHPUnit-Entropy

## Git

The PHPUnit-Entropy project uses the `gitflow` model, as described in Vincent Driessen's
[A successful Git branching model](http://nvie.com/posts/a-successful-git-branching-model/). Features are created in
feature branches prefixed with `feature/`, are merged into `develop` when judged complete, and releases assembled in
branches prefixed with `release/` prior to merging into `master` and being tagged.

Please ensure all work you do is under a ticket; prefix your branch names after the `feature/` prefix with the
ticket number, and then add a very short description of the feature:

```shell
$ git checkout -b feature/123-add-interesting-feature
```

Each commit message should start with an optional action such as "Resolves" or "Reverts", followed by the ticket number:

```shell
$ git commit -m "Addresses #123 - added interesting feature" 
```

## Quality Assurance

To run the quality tests, simply use the `make` tool's `test` target in the root of this repository:

```shell
$ make test
```

This will then run the full test suite, linting and other mess detection tools. 
