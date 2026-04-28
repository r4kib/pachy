# Coder Agent

Neuron AI Coder Agent specialized in PHP and JavaScript web development.

## Features

- **AI-Powered Coding**: Uses Google Gemini for intelligent code generation
- **File Operations**: Advanced file system tools for reading, writing, searching, and text pattern matching
- **Expert Knowledge**: Deep expertise in PHP development and modern JavaScript frameworks
- **Best Practices**: Generates clean, maintainable, and optimized code

## Prerequisites

1. Google Gemini API key
2. Composer packages: `neuron-core/neuron-ai` and `google/auth`

## Installation

The package is already included in the project. Add your API key to `.env`:

```bash
# Create .env file
cp .env.example .env

# Edit .env and add your API key
GEMINI_API_KEY=your_api_key_here
```

## Usage

### Basic Usage

```php
use App\Ai\Agent\Coder;
use NeuronAI\Chat\Messages\UserMessage;

$agent = Coder::make();
$agent->chat(new UserMessage('Create a PHP function to fetch data from an API'));

$message = $agent->getMessage();
echo $message->getContent();
```

### With File Operations

The agent has tools available for file system operations:

```php
use App\Ai\Agent\Coder;
use NeuronAI\Chat\Messages\UserMessage;

$agent = Coder::make();

// Ask the agent to write a file
$agent->chat(new UserMessage('Write a PHP class for database connection'));

// The agent can read and write files using available tools
$agent->chat(new UserMessage('Fix the bugs in ~/app/Services/UserService.php'));

// Search for patterns in your codebase
$agent->chat(new UserMessage('Find all function calls to fetchData in the project'));

$result = $agent->run();
echo $result->getMessage()->getContent();
```

### Agent Tools

The Coder agent automatically has these tools:

1. **read_file**: Read file contents
   - `filepath`: Path to file (relative to project root)

2. **write_file**: Write or overwrite file contents
   - `filepath`: Path to file (relative to project root)
   - `content`: Content to write

3. **search_files**: Search for text in files
   - `pattern`: Text pattern to search
   - `directory`: Optional directory (defaults to project root)
   - `types`: Optional file types (php,js,html,css,txt)

4. **grep_files**: Recursive grep with regex
   - `pattern`: Regular expression pattern
   - `directory`: Optional directory (defaults to project root)
   - `output_format`: "lines" (default) or "files"

### File Search Pattern

The agent can use these tools to navigate and modify your codebase:
- Read existing files to understand structure
- Write new files with generated content
- Search for code patterns and functions
- Find occurrences of code across the project

## System Prompt

The agent is configured with a comprehensive system prompt that makes it an expert:

**Background Roles:**
- Expert AI Coder in PHP and JavaScript web development
- Proficient in modern web technologies, frameworks, and best practices
- Deep knowledge of REST APIs, databases, authentication, security

**Steps Behavior:**
- Analyze coding requests with focus on requirements and constraints
- Write code following best practices and common patterns
- Provide clear documentation and comments for complex logic
- Suggest optimizations and improvements
- Ask clarifying questions for ambiguous requirements

**Output Format:**
- Clean, idiomatic PHP and JavaScript with proper formatting
- Relevant examples and usage comments
- Complete, functional code when applicable
- Explanation of approach and reasoning when helpful
- Markdown for structured output

### Testing

Run the test example:

```bash
php examples/test_coder.php
```

Or use directly via Laravel Zero's `pachy` command:

```bash
php pachy artisan
```

### Alternative Providers

To use a different AI provider, modify the `provider()` method in `App\Ai\Agent\Coder.php`:

```php
// OpenAI Example
use NeuronAI\Providers\OpenAI\OpenAI;

protected function provider(): AIProviderInterface
{
    return new OpenAI(
        key: env('OPENAI_API_KEY'),
        model: env('OPENAI_MODEL', 'gpt-4'),
    );
}

// Anthropic Example
use NeuronAI\Providers\Anthropic\Anthropic;

protected function provider(): AIProviderInterface
{
    return new Anthropic(
        key: env('ANTHROPIC_API_KEY'),
        model: env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
    );
}
```

## Security

- File operations are restricted to the project directory (prevents access outside working directory)
- Path validation ensures safe file system interactions
- Tools provide helpful error messages for common issues

## License

MIT