const rangeSlider = document.getElementById('slider-round');
const input0 = document.getElementById('input-0');
const value0 = parseFloat(input0.value);
const input1 = document.getElementById('input-1');
const value1 = parseFloat(input1.value);

if (rangeSlider) {
	noUiSlider.create(rangeSlider, {
    start: [value0, value1],
		connect: true,
		step: 1,
    range: {
			'min': [value0],
			'max': [value1]
    }
	});

	const input0 = document.getElementById('input-0');
	const input1 = document.getElementById('input-1');
	const inputs = [input0, input1];

	rangeSlider.noUiSlider.on('update', function(values, handle){
		inputs[handle].value = Math.round(values[handle]);
		updateProducts();
	});

	const setRangeSlider = (i, value) => {
		let arr = [null, null];
		arr[i] = value;

		console.log(arr);

		rangeSlider.noUiSlider.set(arr);
	};

	inputs.forEach((el, index) => {
		el.addEventListener('change', (e) => {
			console.log(index);
			setRangeSlider(index, e.currentTarget.value);
		});
	});
}