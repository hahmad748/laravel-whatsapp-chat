# Contributing

Thank you for considering contributing to the Laravel WhatsApp Chat Package! We welcome contributions from the community.

## How to Contribute

### 1. Fork the Repository

Fork the repository on GitHub and clone your fork locally:

```bash
git clone https://github.com/hahmad748/laravel-whatsapp-chat.git
cd laravel-whatsapp-chat
```

### 2. Create a Branch

Create a new branch for your feature or bug fix:

```bash
git checkout -b feature/your-feature-name
# or
git checkout -b bugfix/your-bug-fix
```

### 3. Install Dependencies

Install the package dependencies:

```bash
composer install
npm install
```

### 4. Make Your Changes

- Write your code following the existing code style
- Add tests for new functionality
- Update documentation if needed
- Ensure all tests pass

### 5. Run Tests

Before submitting your changes, make sure all tests pass:

```bash
# Run PHP tests
composer test

# Run frontend tests (if applicable)
npm test
```

### 6. Commit Your Changes

Commit your changes with a clear and descriptive message:

```bash
git add .
git commit -m "Add: New feature description"
```

### 7. Push and Create Pull Request

Push your changes to your fork and create a pull request:

```bash
git push origin feature/your-feature-name
```

Then create a pull request on GitHub.

## Code Style

### PHP

- Follow PSR-12 coding standards
- Use meaningful variable and method names
- Add PHPDoc comments for public methods
- Keep methods small and focused
- Use type hints where possible

### JavaScript/Vue

- Follow Vue.js style guide
- Use meaningful component and variable names
- Add comments for complex logic
- Keep components small and focused
- Use consistent indentation (2 spaces)

## Testing

### Writing Tests

- Write tests for all new functionality
- Test both success and failure scenarios
- Use descriptive test names
- Keep tests simple and focused
- Mock external dependencies

### Test Structure

```php
/** @test */
public function it_can_perform_some_action()
{
    // Arrange
    $data = ['key' => 'value'];
    
    // Act
    $result = $this->service->performAction($data);
    
    // Assert
    $this->assertTrue($result['success']);
}
```

## Documentation

### Updating Documentation

- Update README.md for new features
- Update CHANGELOG.md for all changes
- Add code examples where helpful
- Keep installation instructions up to date

### Documentation Structure

- Clear headings and sections
- Code examples with syntax highlighting
- Step-by-step instructions
- Troubleshooting sections

## Pull Request Guidelines

### Before Submitting

- [ ] All tests pass
- [ ] Code follows style guidelines
- [ ] Documentation is updated
- [ ] CHANGELOG.md is updated
- [ ] No breaking changes (or clearly documented)

### Pull Request Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Tests added/updated
- [ ] All tests pass
- [ ] Manual testing completed

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
```

## Issues

### Reporting Bugs

When reporting bugs, please include:

- Laravel version
- PHP version
- Package version
- Steps to reproduce
- Expected behavior
- Actual behavior
- Error messages/logs

### Feature Requests

When requesting features, please include:

- Use case description
- Proposed solution
- Alternative solutions considered
- Additional context

## Development Setup

### Local Development

1. Clone the repository
2. Install dependencies: `composer install && npm install`
3. Copy `.env.example` to `.env`
4. Configure your environment variables
5. Run tests: `composer test`

### Testing with Real WhatsApp API

1. Get WhatsApp Business API credentials
2. Set `WHATSAPP_USE_MOCK_MODE=false` in `.env`
3. Configure real credentials
4. Test with real WhatsApp numbers

## Release Process

### Versioning

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

### Release Checklist

- [ ] All tests pass
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
- [ ] Version bumped in composer.json
- [ ] Tagged in Git
- [ ] Released on Packagist

## Community

### Getting Help

- Check the documentation first
- Search existing issues
- Create a new issue if needed
- Join our community discussions

### Code of Conduct

- Be respectful and inclusive
- Focus on constructive feedback
- Help others learn and grow
- Follow the golden rule

## License

By contributing to this project, you agree that your contributions will be licensed under the MIT License.

## Thank You

Thank you for contributing to the Laravel WhatsApp Chat Package! Your contributions help make this project better for everyone.
