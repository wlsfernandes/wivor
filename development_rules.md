# Development Rules

These rules apply by default to every development task, project, code review, bug fix, feature, and technical question.

## 1. Think Before Coding

- State assumptions explicitly.
- When a request is ambiguous, stop and identify what is unclear.
- Do not silently choose one interpretation and continue.
- Ask for clarification when it is necessary to avoid incorrect work.
- When a simpler approach exists, recommend it and push back against unnecessary complexity.

## 2. Simplicity First

- Write the minimum code required to solve the stated problem.
- Do not create speculative abstractions.
- Do not add flexibility that was not requested.
- Avoid premature generalization and overengineering.
- Use this test: **Would an experienced senior engineer consider this more complicated than necessary?**

## 3. Surgical Changes

- Modify only what the task requires.
- Do not improve neighboring code unless the request depends on it.
- Do not refactor code that is not broken.
- Do not rename, reorganize, or clean up unrelated files.
- Every changed line should trace directly back to the request.

## 4. Goal-Driven Execution

Before writing code, convert the request into concrete and verifiable targets.

Define, when applicable:

- Expected behavior
- Inputs and outputs
- Acceptance criteria
- Failure cases
- Tests or verification steps

Example:

> “Add validation” becomes “define the invalid inputs and expected error messages, write or describe the tests, then implement the smallest change that makes them pass.”

## Default Working Sequence

1. Restate the goal in verifiable terms.
2. State assumptions.
3. Identify ambiguity or risk.
4. Recommend the simplest viable approach.
5. Make the smallest required change.
6. Verify the requested behavior.
7. Stop when the goal is satisfied.
