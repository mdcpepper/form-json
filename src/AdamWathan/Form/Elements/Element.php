<?php namespace AdamWathan\Form\Elements;

use Illuminate\Support\Arr;

abstract class Element
{
    protected $attributes = array();

    protected function setAttribute($attribute, $value = null)
    {
        if (is_null($value)) {
            return;
        }

        $this->attributes[$attribute] = $value;
    }

    protected function removeAttribute($attribute)
    {
        unset($this->attributes[$attribute]);
    }

    public function getAttribute($attribute, $default = null)
    {
        return Arr::get($this->attributes, $attribute, $default);
    }

    public function data($attribute, $value)
    {
        $this->setAttribute('data-'.$attribute, $value);
        return $this;
    }

    public function attribute($attribute, $value)
    {
        $this->setAttribute($attribute, $value);
        return $this;
    }

    public function clear($attribute)
    {
        if (! isset($this->attributes[$attribute])) {
            return $this;
        }

        $this->removeAttribute($attribute);
        return $this;
    }

    public function addClass($class)
    {
        if (isset($this->attributes['class'])) {
            $class = $this->attributes['class'] . ' ' . $class;
        }

        $this->setAttribute('class', $class);
        return $this;
    }

    public function removeClass($class)
    {
        if (! isset($this->attributes['class'])) {
            return $this;
        }

        $class = trim(str_replace($class, '', $this->attributes['class']));
        if ($class == '') {
            $this->removeAttribute('class');
            return $this;
        }

        $this->setAttribute('class', $class);
        return $this;
    }

    public function id($id)
    {
        $this->setId($id);
        return $this;
    }

    protected function setId($id)
    {
        $this->setAttribute('id', $id);
    }

    abstract public function render();

    public function __toString()
    {
        return $this->render();
    }

    protected function renderAttributes()
    {
        $result = '';

        foreach ($this->attributes as $attribute => $value) {
            $result .= " {$attribute}=\"{$value}\"";
        }

        return $result;
    }

    public function parsley(string $label)
    {
        $label = __($label);
        $type  = $this->getAttribute('type');

        if ($this->getAttribute('required')) {
            $this->attribute('data-parsley-required-message', __('validation.required', [
                'attribute' => $label,
            ]));
        }

        if ($maxlength = $this->getAttribute('maxlength')) {
            $this->attribute('data-parsley-maxlength-message', __('validation.max.string', [
                'attribute' => $label,
                'max'       => $maxlength,
            ]));
        }

        if ($minlength = $this->getAttribute('minlength')) {
            $this->attribute('data-parsley-minlength-message', __('validation.min.string', [
                'attribute' => $label,
                'min'       => $minlength,
            ]));
        }

        if ($this->getAttribute('pattern')) {
            $this->attribute('data-parsley-pattern-message', __('validation.regex', [
                'attribute' => $label,
            ]));
        }

        if ($type === 'email') {
            $this->attribute('data-parsley-email-message', __('validation.email', [
                'attribute' => $label,
            ]));
        }

        if ($type === 'num') {
            if ($min = $this->getAttribute('min')) {
                $this->attribute('data-parsley-min-message', __('validation.min.numeric', [
                    'attribute' => $label,
                    'min'       => $min,
                ]));
            }

            if ($max = $this->getAttribute('max')) {
                $this->attribute('data-parsley-max-message', __('validation.max.numeric', [
                    'attribute' => $label,
                    'max'       => $max,
                ]));
            }
        }
    }

    public function translatable()
    {
        $this->addClass('translatable');
    }

    public function __call($method, $params)
    {
        $params = count($params) ? $params : array($method);
        $params = array_merge(array($method), $params);
        call_user_func_array(array($this, 'attribute'), $params);
        return $this;
    }
}
