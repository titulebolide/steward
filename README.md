# STEWARD - Client and Server Integrable Streaming Bundle

Client and Server integrable system to stream video files, na matter their extension.
It transcodes on the fly the files and serves them to the Client
Supports serveral different streams at a time

Benchmarked successfully on a mere laptop up to 10 simultaneous transcodings

## Architecture
**Two servers**
 - A backend that handles every transcoders (running thanks to FFMpeg) and their processes (Python/Flask server)
 - A static file server (it can be the same that the rest of te application), that serves the video segements generated by FFMpeg and serves also the frontend
