# Baker Server Example

This is a collection of PHP scripts implementing a basic version of the server required to support auto-renewable subscriptions in [Baker Framework](https://github.com/Simbul/baker).

This server conforms to the [Baker Server API](https://github.com/Simbul/baker/wiki/Baker-Server-API).

# Disclaimer

This server is provided as an example and it should never be used in production.

# Configuration

To verify receipt with the App Store you need to put your app shared secret in the `shared_secret.txt` file. See `shared_secret.txt.example` for instructions.
You can generate/retrieve your app shared secret in iTunes Connect.

You also need a SQLite databse. You can get an empty one by renaming `baker.sqlite3.example` to `baker.sqlite3` inside the `db/` folder.
