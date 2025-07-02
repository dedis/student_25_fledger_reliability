from mergexp import *

net = Network('net2')

def makeNode(i: int):
    name = f"n{i}"
    return net.node(name, proc.cores>=1, memory.capacity>=mb(512))

n0 = makeNode(0)
n1 = makeNode(1)
central = net.node('central', proc.cores>=2, memory.capacity>=mb(512))

sna = [n0, n1, central]

link = net.connect(sna)
link[n0].socket.addrs = ip4('10.0.0.1/16')
link[n1].socket.addrs = ip4('10.0.0.2/16')
link[central].socket.addrs = ip4('10.0.128.128/16')

experiment(net)
