<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'De :attribute moet zijn geaccepteerd.',
    'active_url'           => 'De :attribute is geen geldige URL.',
    'after'                => 'De :attribute moet een datum zijn na :date.',
    'alpha'                => 'De :attribute mag alleen uit letters bestaan.',
    'alpha_dash'           => 'De :attribute mag alleen bestaan uit letters, cijfers, en streepjes.',
    'alpha_num'            => 'De :attribute mag alleen bestaan uit letters en cijfers.',
    'array'                => 'De :attribute moet een array zijn.',
    'before'               => 'De :attribute moet een datum zijn voor :date.',
    'between'              => [
        'numeric' => 'De :attribute moet tussen :min en :max zijn.',
        'file'    => 'De :attribute moet tussen :min en :max kilobytes zijn.',
        'string'  => 'De :attribute moet tussen :min en :max karakters zijn.',
        'array'   => 'De :attribute moet tussen :min en :max items zijn.',
    ],
    'boolean'              => 'The :attribute field must be true or false.',
    'confirmed'            => 'The :attribute confirmation does not match.',
    'date'                 => 'De :attribute is niet een correct formaat.',
    'date_format'          => 'The :attribute does not match the format :format.',
    'different'            => 'The :attribute and :other must be different.',
    'digits'               => 'The :attribute must be :digits digits.',
    'digits_between'       => 'The :attribute must be between :min and :max digits.',
    'email'                => 'Het emailadres is niet correct.',
    'filled'               => 'The :attribute field is required.',
    'exists'               => 'The selected :attribute is invalid.',
    'image'                => 'De :attribute moet een plaatje zijn.',
    'in'                   => 'The selected :attribute is invalid.',
    'integer'              => 'The :attribute must be an integer.',
    'ip'                   => 'The :attribute must be a valid IP address.',
    'max'                  => [
        'numeric' => 'De :attribute mag niet groter zijn dan :max.',
        'file'    => 'De :attribute mag niet groter zijn dan :max kilobytes.',
        'string'  => 'De :attribute mag niet groter zijn dan :max karakters.',
        'array'   => 'De :attribute mag niet uit meer dan :max items bestaan.',
    ],
    'mimes'                => 'De :attribute moet een bestand zijn van het type: :values.',
    'min'                  => [
        'numeric' => 'De :attribute moet minimaal :min lang zijn.',
        'file'    => 'De :attribute moet minimaal :min kilobytes zijn.',
        'string'  => 'De :attribute moet minimaal :min karakters zijn.',
        'array'   => 'De :attribute moet minimaal :min items bevatten.',
    ],
    'not_in'               => 'The selected :attribute is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'regex'                => 'The :attribute format is invalid.',
    'required'             => 'The :attribute field is required.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'string'               => 'The :attribute must be a string.',
    'timezone'             => 'The :attribute must be a valid zone.',
    'unique'               => 'The :attribute has already been taken.',
    'url'                  => 'The :attribute format is invalid.',

    'answerscount'         => 'Minimaal 2 antwoorden nodig.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'Aangepast bericht',
            
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
