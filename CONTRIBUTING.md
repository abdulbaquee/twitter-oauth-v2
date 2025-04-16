# Contributing to TwitterOAuthV2

Thank you for considering contributing to TwitterOAuthV2! This document outlines the process for contributing to the project.

## Code of Conduct

This project adheres to the Contributor Covenant [code of conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code. Please report unacceptable behavior to the project maintainers.

## How to Contribute

### Reporting Bugs

If you find a bug in the library, please create an issue on GitHub with the following information:

- A clear, descriptive title
- A detailed description of the issue
- Steps to reproduce the bug
- Expected behavior
- Actual behavior
- PHP version and other relevant environment details
- Code samples or test cases that demonstrate the issue

### Suggesting Enhancements

If you have an idea for a new feature or improvement, please create an issue on GitHub with the following information:

- A clear, descriptive title
- A detailed description of the proposed enhancement
- Any relevant examples or use cases
- If applicable, references to Twitter API documentation

### Pull Requests

1. Fork the repository
2. Create a new branch for your feature or bugfix (`git checkout -b feature/your-feature-name` or `git checkout -b fix/your-bugfix-name`)
3. Make your changes
4. Run tests to ensure your changes don't break existing functionality
5. Commit your changes with clear, descriptive commit messages
6. Push your branch to your fork
7. Submit a pull request to the main repository

#### Pull Request Guidelines

- Follow the existing code style and conventions
- Include tests for new features or bugfixes
- Update documentation as necessary
- Keep pull requests focused on a single change
- Link to any relevant issues

## Development Setup

1. Clone the repository
2. Install dependencies with Composer: `composer install`
3. Run tests: `composer test`

## Coding Standards

This project follows PSR-12 coding standards. You can check your code with:

```bash
composer cs-check
```

And automatically fix many issues with:

```bash
composer cs-fix
```

## Running Tests

```bash
composer test
```

## Documentation

If you're adding new features, please update the documentation accordingly. This includes:

- Updating the README.md file
- Adding or updating PHPDoc comments in the code
- Updating or adding example files if necessary

## License

By contributing to this project, you agree that your contributions will be licensed under the project's [MIT License](LICENSE.md).
