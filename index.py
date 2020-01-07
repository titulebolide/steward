import sys
import os
import subprocess
import numpy as np
from threading import Thread
import time
import signal
from urllib.parse import unquote
from flask import Flask
from flask_cors import CORS, cross_origin
import copy
import string
import random

killerTimeout = 15


def makeId(stringLength):
    letters = string.ascii_letters
    return ''.join(random.choice(letters) for i in range(stringLength))


class Worker(Thread):
    def __init__(self, src, ss, id):
        Thread.__init__(self)
        self.src = src
        self.ss = ss
        self.id = id
        self.pid = None
        self.ka = time.time()
        self.cmd = """
        cd hls
        ffmpeg -ss """+str(ss)+""" -i ../storage/"""+str(src)+""" -preset ultrafast -vcodec libx264 -vprofile baseline -acodec aac -y -hls_time 2 -hls_list_size 0 -hls_segment_size 500000 """+str(id)+""".m3u8\
        """

    def run(self):
        self.pid = subprocess.Popen(self.cmd, shell=True).pid

    def kill(self):
        if self.pid is None:
            return
        subprocess.call("pkill --parent " + str(self.pid), shell=True)
        time.sleep(1)
        subprocess.call("cd hls ; rm " + self.id + "*", shell=True)

    def keepAlive(self):
        self.ka = time.time()

class Transcoder(Thread):
    def __init__(self):
        Thread.__init__(self)
        self.workers = {}
        self.tst = {}
        self.kill = False

    def createWorker(self, src, ss, id):
        self.workers[id] = Worker(src,ss,id)
        self.workers[id].start()

    def keepAliveWorker(self, id):
        if id in self.workers:
            self.workers[id].keepAlive()

    def killWorker(self,id):
        if id in self.workers:
            self.workers[id].kill()
            self.workers.pop(id)

    def run(self):
        while not self.kill:
            workersCopy = copy.copy(self.workers)
            for id, worker in workersCopy.items():
                if time.time() - worker.ka > killerTimeout and id in self.workers:
                    self.killWorker(id)
            time.sleep(killerTimeout)

    def kill(self):
        self.kill = True
        for id, worker in self.workers.items():
            self.killWorker(id)

app = Flask(__name__, static_folder="static")
cors = CORS(app)
app.config['CORS_HEADERS'] = 'Content-Type'

t = Transcoder()
t.start()

@app.route("/transcode/<path:src>/<ss>", defaults={"previd": None})
@app.route("/transcode/<path:src>/<ss>/<previd>")
@cross_origin()
def transcode(src, ss, previd):
    id = makeId(10)
    subprocess.call("cd hls ; echo '#EXTM3U' >> "+str(id)+".m3u8", shell=True)
    t.createWorker(unquote(src), ss, id)
    if previd is not None :
        t.killWorker(previd)
    return id

@app.route("/duration/<path:src>")
@cross_origin()
def duration(src):
    full_src = "storage/" + src
    result = subprocess.run(["ffprobe", "-v", "error", "-show_entries",
                             "format=duration", "-of",
                             "default=noprint_wrappers=1:nokey=1", full_src],
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT)
    return result.stdout

@app.route("/keepalive/<id>")
@cross_origin()
def keepalive(id):
    t.keepAliveWorker(id)
    return ""

if __name__ == "__main__":
    app.run()
