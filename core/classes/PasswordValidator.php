<?php

class PasswordValidator
{
    public static function validate($password)
    {
        if (empty($password)) {
            return ['valid' => false, 'message' => 'Password cannot be empty.'];
        }

        if (strlen($password) < 8) {
            return ['valid' => false, 'message' => 'Password must be at least 8 characters long.'];
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one uppercase letter.'];
        }

        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one lowercase letter.'];
        }

        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one number.'];
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one special character.'];
        }

        return ['valid' => true, 'message' => 'Password is valid.'];
    }


    public static function getRequirementsHtml()
    {
        return '<div class="text-xs text-gray-500 mt-1">
            <p>Password must contain:</p>
            <ul class="list-disc list-inside ml-2">
                <li>At least 8 characters</li>
                <li>At least 1 uppercase letter</li>
                <li>At least 1 lowercase letter</li>
                <li>At least 1 number</li>
                <li>At least 1 special character</li>
            </ul>
        </div>';
    }

    public static function getRequirementsText()
    {
        return 'Password must contain at least 8 characters, 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.';
    }
}
