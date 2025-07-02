from mergexp import *

net = Network('net33')

def makeNode(i: int):
    name = f"n{i}"
    return net.node(name, proc.cores>=1, memory.capacity>=mb(512))

central = net.node('central', proc.cores>=2, memory.capacity>=mb(512))

sna = [makeNode(i) for i in range(33)]
sna.append(central)

link = net.connect(sna, capacity==mbps(1), latency==ms(10))

for i in range(33):
    suffix = str(i + 1)
    link[sna[i]].socket.addrs = ip4(f"10.0.0.{suffix}/16")
link[central].socket.addrs = ip4("10.0.128.128/16")

experiment(net)
