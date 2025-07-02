# Experiments

This file describes the different experiments we make

## Chat experiments

Runs several instances that both broadcast a message to an instance, and wait for a specific message (coming from another instance).
The simulation ends for an instance when it receives a message.

The simulation waits for every node to receive their message.

Note: exp01 is an exception, it only runs two nodes, one node sending a message to the other.

## DHT experiments

Creates a realm on the signaling server.
Waits for every instance in the experiment to have joined that realm.
