<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    /**
     * Validate an input array against rules and return validated data or throw AppException with 422.
     *
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     * @param array<string, string> $customMessages
     * @return array<string, mixed>
     * @throws AppException
     */
    public static function validate(array $data, array $rules, array $customMessages = []): array
    {
        $errors = [];
        $validated = [];

        foreach ($rules as $field => $ruleString) {
            $ruleList = explode('|', $ruleString);
            $isNullable = in_array('nullable', $ruleList, true);
            $isRequired = in_array('required', $ruleList, true);

            $value = $data[$field] ?? null;
            $isEmpty = $value === null || (is_string($value) && trim($value) === '');

            if ($isEmpty) {
                if ($isRequired) {
                    $errors[$field] = $customMessages["{$field}.required"] ?? "Field {$field} wajib diisi.";
                } elseif ($isNullable) {
                    $validated[$field] = null;
                }
                continue;
            }

            foreach ($ruleList as $rule) {
                if ($rule === 'required' || $rule === 'nullable') {
                    continue;
                }

                $ruleName = $rule;
                $param = null;

                if (str_contains($rule, ':')) {
                    [$ruleName, $param] = explode(':', $rule, 2);
                }

                $failed = false;
                $defaultMsg = "Field {$field} tidak valid.";

                switch ($ruleName) {
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $failed = true;
                            $defaultMsg = 'Format email tidak valid.';
                        }
                        break;

                    case 'min':
                        $min = (int) $param;
                        if (is_string($value) && mb_strlen($value) < $min) {
                            $failed = true;
                            $defaultMsg = "Field {$field} minimal {$min} karakter.";
                        } elseif (is_numeric($value) && (float) $value < $min) {
                            $failed = true;
                            $defaultMsg = "Nilai {$field} minimal {$min}.";
                        }
                        break;

                    case 'max':
                        $max = (int) $param;
                        if (is_string($value) && mb_strlen($value) > $max) {
                            $failed = true;
                            $defaultMsg = "Field {$field} maksimal {$max} karakter.";
                        } elseif (is_numeric($value) && (float) $value > $max) {
                            $failed = true;
                            $defaultMsg = "Nilai {$field} maksimal {$max}.";
                        }
                        break;

                    case 'int':
                        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                            $failed = true;
                            $defaultMsg = "Field {$field} harus berupa angka bulat.";
                        }
                        break;

                    case 'in':
                        $allowed = explode(',', (string) $param);
                        if (!in_array((string) $value, $allowed, true)) {
                            $failed = true;
                            $defaultMsg = "Nilai {$field} tidak termasuk pilihan yang sah.";
                        }
                        break;

                    case 'regex':
                        if (!preg_match((string) $param, (string) $value)) {
                            $failed = true;
                            $defaultMsg = "Format {$field} tidak sesuai.";
                        }
                        break;

                    case 'phone':
                        // Indonesian mobile phone: 08xxx or 628xxx (8 to 13 digits after prefix)
                        if (!preg_match('/^(0|62)8[0-9]{7,12}$/', (string) $value)) {
                            $failed = true;
                            $defaultMsg = 'Format nomor telepon tidak valid. Gunakan format 08xx atau 628xx.';
                        }
                        break;
                }

                if ($failed) {
                    $errors[$field] = $customMessages["{$field}.{$ruleName}"] ?? $defaultMsg;
                    break; // Stop evaluating further rules for this field
                }
            }

            if (!isset($errors[$field])) {
                $validated[$field] = is_string($value) ? trim($value) : $value;
            }
        }

        if (!empty($errors)) {
            throw new AppException(
                'VALIDATION_ERROR',
                'Periksa kembali isian Anda.',
                422,
                $errors
            );
        }

        return $validated;
    }
}
