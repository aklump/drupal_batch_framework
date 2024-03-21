# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

- None

## [0.0.33] - 2024-03-21

### Added

- \AKlump\Drupal\BatchFramework\Batch\OperationInterface::getRemainingTime
- Previously the operation timeout was handled exclusively by \AKlump\Drupal\BatchFramework\Batch\Operator::handleOperation. Now the operation class will receive the timeout value as `$batch_context['max_execution_seconds']`, which will allow it to exit early in respect to that value, if it so chooses.
- \AKlump\Drupal\BatchFramework\Batch\DrupalBatchAPIOperationBase::getRemainingTime was added to take advantage of these changes; use this method in your child classes to handle an earlier exit based on timeout. Otherwise the Operator will manage time as before.

## [0.0.28] - 2023-12-08

### Changed

- **`BatchDefinitionInterface::handleFailedBatch` has been updated with new parameters.**
- Refactored the logic to handle failed batch operations in the DrupalBatchAPIBase class. A separate array for exceptions has been made and unnecessary keys from the batch results have been excluded. Changes were also made in the BatchDefinitionInterface and Operator as part of this update.
