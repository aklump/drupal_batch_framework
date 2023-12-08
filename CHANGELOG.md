# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

- None

## [0.0.28] - 2023-12-08

### Changed

- **`BatchDefinitionInterface::handleFailedBatch` has been updated with new parameters.**
- Refactored the logic to handle failed batch operations in the DrupalBatchAPIBase class. A separate array for exceptions has been made and unnecessary keys from the batch results have been excluded. Changes were also made in the BatchDefinitionInterface and Operator as part of this update.

  
