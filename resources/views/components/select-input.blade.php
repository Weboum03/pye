@props(['disabled' => false, 'options' => $options, 'value' => $value])

<select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) !!} >
    @foreach($options as $option)
    <option {{ ($value == $option) ? 'selected' : '' }} value="{{$option}}" > {{$option}} </option>
    @endforeach
</select>
