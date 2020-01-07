start:
	bash -c "export FLASK_ENV=development && python3 index.py"

cleanup:
	cd hls && rm *
