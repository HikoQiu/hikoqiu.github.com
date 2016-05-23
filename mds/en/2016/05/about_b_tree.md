---

layout: post  
title: About B-Tree
subtitle:   
author: Hiko  
category: tech
tags: Data  Structre, B-Tree, B+Tree, B\*Tree, database index  
ctime: 2016-05-23 13:14:03  
lang: en  

---

### Contents

- B-Trees
- B+ Trees
- B* Trees
- Analysis

### B-Trees

#### i. Properties of B-Trees

**B-trees** address effectively all of the major problems encountered when implementing disk-based search trees:

- B-trees are always height balanced, with all leaf nodes at the same level.
- Update and search operations affect only a few disk blocks. The fewer the number of disk blocks affected, the less disk I/O is required.
- B-trees keep related records (that is, records with similar key values) on the same disk block, which helps to minimize disk I/O on searches due to locality of reference.
- B-trees guarantee that every node in the tree will be full at least to a certain minimum percentage. This improves space efficiency while reducing the typical number of disk fetches necessary during a search or update operation.

A B-tree of order mm is defined to have the following shape properties:

- The root is either a leaf or has at least two children.
- Each internal node, except for the root, has between *[m/2]* and *m* children.
- All leaves are at the same level in the tree, so the tree is always height balanced.

Figure [11.6.1] shows a B-tree of order four. Each node contains up to three keys, and internal nodes have up to four children.

![Btree Example](http://algoviz.org/OpenDSA/Books/CS3114/html/_images/BTexamp.png)

*Figure [11.6.1]: A B-tree of order **four**.*

#### ii. B-Trees insertion

**B-tree insertion** is a generalization of 2-3 tree insertion. 

1. The first step is to find the leaf node that should contain the key to be inserted,space permitting. 
2. If there is room in this node, then insert the key. 
3. If there is not, then split the node into two and promote the middle key to the parent. 
4. If the parent becomes full, then it is split in turn, and its middle key promoted.

**Further more**: 
Note that this insertion process is guaranteed to keep all nodes at least half full. For example, when we attempt to insert into a full internal node of a B-tree of order four, there will now be five children that must be dealt with. The node is split into two nodes containing two keys each, thus retaining the B-tree property. The middle of the five children is promoted to its parent.


### B+ Trees

#### i. Difference between B+ Trees and B-Trees / BST

The **B+ tree** is essentially a mechanism for managing a sorted array-based list, where the list is broken into chunks.

The most significant difference between the B+ tree and the BST (Binary Search Tree) or the standard B-tree is that :
- **the B+ tree stores records only at the leaf nodes**. Internal nodes store key values, but these are used solely as placeholders to guide the search. This means that internal nodes are significantly different in structure from leaf nodes. Internal nodes store keys to guide the search, associating each key with a pointer to a child B+ tree node. Leaf nodes store actual records, or else keys and pointers to actual records in a separate disk file if the B+ tree is being used purely as an index. 
- **The leaf nodes of a B+ tree are normally linked together to form a doubly linked list**. Thus, the entire collection of records can be traversed in sorted order by visiting all the leaf nodes on the linked list. 

*Java-like pseudocode representation for the B+ Tree node interface*

	/** Interface for B+ Tree nodes */
	public interface BPNode<Key,E> {
  		public boolean isLeaf();
  		public int numrecs();
  		public Key[] keys();
	}

#### ii. B+ Tree insertion

B+  tree insertion is similar to B-tree insertion. 

1. First, the leaf ***L*** that should contain the record is found. 
2. If ***L*** is not full, then the new record is added, and no other B+ tree nodes are affected. 
3. If ***L*** is already full, split it in two (dividing the records evenly among the two nodes) and promote a copy of the least-valued key in the newly formed right node. As with the 2-3 tree, promotion might cause the parent to split in turn, perhaps eventually leading to splitting the root and causing the B+B+ tree to gain a new level. 

B+ tree insertion keeps all leaf nodes at equal depth. 


#### ii. B+ Tree deletion

To delete record **R** from the B+ tree: 

1. first locate the leaf **L** that contains **R**. 
2. If **L** is more than half full, then we need only remove **R**, leaving **L** still at least half full. 
3. If deleting a record reduces the number of records in the node below the minimum threshold (called an **underflow**), then we must do something to keep the node sufficiently full.   
3.1 The first choice is to look at the node's adjacent siblings to determine if they have a spare record that can be used to fill the gap.   
If so, then enough records are transferred from the sibling so that both nodes have about the same number of records. **This is done so as to delay as long as possible the next time when a delete causes this node to underflow again**. This process might require that the parent node has its placeholder key value revised to reflect the true first key value in each node.
4. If neither sibling can lend a record to the under-full node (call it ***N***), then ***N*** must give its records to a sibling and be removed from the tree. There is certainly room to do this, because the sibling is at most half full (remember that it had no records to contribute to the current node), and ***N*** has become less than half full because it is under-flowing. This merge process combines two subtrees of the parent, which might cause it to underflow in turn. **If the last two children of the root merge together, then the tree loses a level. **

### B* Trees (B+ Tree Variant)

The B* tree is identical to the B+ tree, **except for the rules used to split and merge nodes**.  

- **Instead of splitting a node in half when it overflows, the B* tree gives some records to its neighboring sibling, if possible. **

- If the sibling is also full, then these two nodes split into three. Similarly, when a node underflows, it is combined with its two siblings, and the total reduced to two nodes. Thus, the nodes are always at least two thirds full.

### Analysis

Typical database applications use extremely high branching factors, perhaps 100 or more. Thus, in practice the B-tree and its variants are extremely shallow.

#### i. Examle, B(*) Tree 's order  = 100 (Threshold)

> IMPORTANT: Insertion and deletion guarante to keep all nodes at least half full.

##### 1. height one

- A B-B+ tree with height one (that is, just a single leaf node) can have at most 100 records. 

##### 2. height two

- A B+ tree with height two (a root internal node whose children are leaves) must have **at least 100 records** (2 leaves with 50 records each). 
- It has at most **10,000 records** (100 leaves with 100 records each).

##### 3. height three

- A B+ tree with height three must have **at least 5000 records** (two second-level nodes with 50 children containing 50 records each) and **at most one million records** (100 second-level nodes with 100 full children each).

##### 4. height four

- A B+ tree with height four must have at least 250,000 records and at most 100 million records. 

###  Reference 

- [Algoviz.org / B-Trees](http://algoviz.org/OpenDSA/Books/CS3114/html/BTree.html)

