# Pachy

Minimalist Coding Agent written in PHP using Laravel Zero and Neuron AI.

## Installation

You can install Pachy globally using Composer:

```bash
composer global require r4kib/pachy:*
```

Ensure your global composer bin directory is in your system's PATH.

## Setup Authentication

Before using the AI features, you need to configure your LLM provider. Pachy supports OpenAI, Anthropic, and Gemini.

Run the following command and follow the prompts:

```bash
pachy auth
```

You will be asked to:
- Select an LLM Provider (OpenAI, Anthropic, Gemini).
- Enter your API Key.
- Specify a default model name (e.g., `gpt-4o`, `claude-3-5-sonnet-latest`).

Settings are stored in `~/.pachy/settings.json`.

## Usage: Coder Agent

The Coder Agent is an interactive AI assistant that can help you write, refactor, and explore your codebase. It has access to powerful tools for file manipulation, searching, and structural analysis.

To start the agent:

```bash
pachy coder
```

### Key Features:
- **Interactive Chat**: Natural language interface for coding tasks.
- **Tool-Enabled**: The agent can read, write, search, and analyze files automatically.
- **Human-in-the-loop**: All sensitive operations (like writing or deleting files) require your explicit approval.
- **Context-Aware**: Understands your project structure and file contents.

## License
GPL-3.0-or-later
