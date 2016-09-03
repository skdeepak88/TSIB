# TSIB

General Notes
=============
- The pages will work out of the box if you use the docker setup included in the repository.
- Just download the whole repo and use the `docker-compose.yml` file to spin up the servers.
- Please read below for the scope I have covered.

Frontend
--------
- Please note that I haven't done any browser testing due to time restrictions.
- I have displayed 6 results by default to complement the layout, instead of 5. But that can be easily changes via global variable defined on top of `youtube.js` file.

Backend
-------
- The file browser uses a special URL routing so it has to have a specific `nginx` setup. I have included the `nginix` config file with this repo.
- If used `docker` to run the project that should work out of the box.
- Preview supports `jpg, gif, png`, and other `text` files including `php` files. Any special type of files like `doc, xls` can be easily added.
- Root path can be any system path and not limited to `www`. Set them on the configuration and can browse the files then on.
- Extensions filter should be comma separated values. They will get exploded into array and set to `FileBrowser` class.
